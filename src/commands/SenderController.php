<?php
namespace macfly\streamlog\commands;

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
        $wait = 0.1;
        $max_wait = 2;
        $tries = 0;
        while (true) {
            if (($rslt = $redis->executeCommand('LPOP', [$this->module->redisTarget->key])) !== null) {
                return $rslt;
            }

            $tries++;
            sleep($wait);
            $wait = min($max_wait, $tries/10 + $wait);
        }
    }
}
