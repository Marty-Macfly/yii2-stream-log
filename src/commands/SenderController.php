<?php
namespace macfly\streamlog\commands;

use Exception;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Json;

class SenderController extends Controller
{
    public function actionStart()
    {
        $target = $this->module->getElasticsearchTarget();

        while ($rslt = $this->pop()) {
            list($context, $messages) = Json::decode($rslt);
            do {
                try {
                    $this->stdout("Flushing logs to elasticsearch" . PHP_EOL);
                    $target->setContextMessage($context);
                    $target->messages = $messages;
                    $this->stdout("Flushing " . count($messages) . " line(s) of logs to elasticsearch" . PHP_EOL);
                    $target->export();
                    break;
                } catch (\Exception $error) { // Retry on error
                    $this->stdout(sprintf("Try again: %s%s", $error, PHP_EOL));
                }
            } while (true);
        }

        ExitCode::OK;
    }

    protected function pop()
    {
        $redis = $this->module->redisTarget->getRedis();
        while (true) {
            try
            {
                if (($rslt = $redis->blpop($this->module->redisTarget->key, 0)) !== null) {
                    return $rslt;
                }
            }
            catch (Exception $e)
            {
                Yii::error($e->getMessage());
            }  
        }
    }
}
