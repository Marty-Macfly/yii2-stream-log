<?php
namespace macfly\streamlog\commands;

use Yii;
use yii\console\Controller;
use yii\helpers\Json;

use macfly\streamlog\Module;
use macfly\streamlog\ElasticsearchTarget;

class SenderController extends Controller
{
    public function actionStart()
    {
        $module = Module::getInstance();

        if (!isset($module->elasticsearchTarget['class'])) {
            $module->elasticsearchTarget['class'] = ElasticsearchTarget::className();
        }

        $target = Yii::createObject($module->elasticsearchTarget);

        while ($rslt = $module->pop()) {
            list($context, $messages) = Json::decode($rslt);
            do {
                try {
                    printf("Flushing logs to elasticsearch%s", PHP_EOL);
                    $target->setContextMessage($context);
                    $target->messages = $messages;
                    $target->export();
                    break;
                } catch (\Exception $error) { // Retry on error
                    printf("Try again: %s%s", $error, PHP_EOL);
                }
            } while (true);
        }
    }
}
