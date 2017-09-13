<?php

namespace macfly\streamlog;

use Yii;
use yii\base\BootstrapInterface;
use yii\di\Instance;
use yii\log\Dispatcher;
use yii\redis\Connection as RedisConnection;

class Module extends \yii\base\Module implements BootstrapInterface
{
    /** @var array elasticsearchTarget configuration. */
    public $elasticsearchTarget = null;
    /** @var string redis component configuration use as a buffer. */
    public $redis               = null;
    /** @var array redisTarget configuration. */
    public $redisTarget         = null;
    /** @var string Redis list key. */
    public $key                 = 'logs';

    /** @inheritdoc */
    public function bootstrap($app)
    {
        if ($app->has('log') && ($log = $app->getLog()) instanceof Dispatcher) {
            if (!isset($this->redisTarget['class'])) {
                $this->redisTarget['class'] = RedisTarget::className();
            }

            if (!isset($this->redisTarget['redis'])) {
                $this->redisTarget['redis'] = $this->getRedis();
            }

            $this->redisTarget['key'] = $this->key;
            $log->targets[]           = Yii::createObject($this->redisTarget);
        }
    }

    /**
     * Initializes the redis Connection component.
     */
    public function getRedis()
    {
        return Instance::ensure($this->redis, RedisConnection::className());
    }

    /** @inheritdoc */
    public function init()
    {
        parent::init();

        if (Yii::$app instanceof \yii\console\Application) {
            $this->controllerNamespace = __NAMESPACE__ . '\commands';
        }
    }

    public function pop()
    {
        $wait     = 0.1;
        $max_wait = 2;
        $tries    = 0;
        while (true) {
            if (($rslt = $this->getRedis()->executeCommand('LPOP', [$this->key])) !== null) {
                return $rslt;
            }

            $tries++;
            sleep($wait);
            $wait = min($max_wait, $tries/10 + $wait);
        }
    }
}
