<?php

namespace macfly\streamlog;

use Yii;
use yii\base\BootstrapInterface;
use yii\di\Instance;
use yii\log\Dispatcher;

class Module extends \yii\base\Module implements BootstrapInterface
{
    /** @var array elasticsearchTarget configuration. */
    public $elasticsearchTarget = null;
    /** @var array redisTarget configuration. */
    public $redisTarget = null;

    /**
     * Add redis target to logdispatcher
     */
    public function bootstrap($app)
    {
        if ($this->redisTarget !== null
            && $app->has('log')
            && ($log = $app->getLog()) instanceof Dispatcher) {
            $this->redisTarget = Instance::ensure($this->redisTarget, RedisTarget::class);
            $log->targets[] = $this->redisTarget;
        }
    }

    /** @inheritdoc */
    public function init()
    {
        parent::init();

        if (Yii::$app instanceof \yii\console\Application) {
            $this->controllerNamespace = __NAMESPACE__ . '\commands';
        }
    }

    public function getElasticsearchTarget()
    {
        return Instance::ensure($this->elasticsearchTarget, ElasticsearchTarget::class);
    }
}
