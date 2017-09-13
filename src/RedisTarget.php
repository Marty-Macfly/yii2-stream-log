<?php

namespace macfly\streamlog;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Json;
use yii\redis\Connection;
use yii\di\Instance;

// class RedisTarget extends \index0h\log\RedisTarget
class RedisTarget extends \yii\log\Target
{
    /**
     * @var Connection|string|array the Redis [[Connection]] object or the application component ID of the Redis [[Connection]].
     * This can also be an array that is used to create a redis [[Connection]] instance in case you do not want do configure
     * redis connection as an application component.
     */
    public $redis      = null;
    /** @var string Redis list key. */
    public $key        = 'logs';
    /** @var bool Whether to log a message containing the current user name and ID. */
    public $logUser    = false;
    /** @var bool Whether to log a message containing the app name. */
    public $logApp     = false;
    /** @var bool Whether to log a message containing the request id from tacker component. */
    public $logTracker = false;
    /** @var bool Whether to log a message containing the current session ID. */
    public $logUserIp  = false;
    /** @var bool Whether to log a message containing the current session ID. */
    public $logSession = false;

    /** @inheritdoc */
    public function init()
    {
        parent::init();
        $this->redis = Instance::ensure($this->redis, Connection::className());
    }

    /**
     * Generates the context information to be logged.
     *
     * @return array
     */
    protected function getExtraContextMessage()
    {
        if ($this->logUser === true) {
            /* @var $user \yii\web\User */
            $user    = Yii::$app->has('user', true) ? Yii::$app->get('user') : null;
            if ($user && ($identity = $user->getIdentity(false))) {
                $context['user'] = ($user && ($identity = $user->getIdentity(false))) ? $identity->getId() : '-';
            }
        }

        if ($this->logApp === true) {
            $context['app'] = Yii::$app->name;
        }

        if ($this->logTracker === true) {
            $context['id'] = Yii::$app->has('tracker') ? Yii::$app->tracker->getId() : '-';
        }

        if ($this->logUserIp === true) {
            $request = Yii::$app->getRequest();
            $context['ip'] = $request instanceof Request ? \Yii::$app->getRequest()->getUserIP() : '-';
        }

        if ($this->logSession === true) {
            /* @var $session \yii\web\Session */
            $session   = Yii::$app->has('session', true) ? Yii::$app->get('session') : null;
            $context['session'] = $session && $session->getIsActive() ? $session->getId() : '-';
        }

        return $context;
    }

    /**
     * @inheritdoc
     */
    public function export()
    {
        $this->redis->lpush($this->key, Json::encode([
            $this->getExtraContextMessage(),
            $this->messages,
        ]));
    }
}
