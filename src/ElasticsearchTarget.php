<?php

namespace macfly\streamlog;

use Yii;

class ElasticsearchTarget extends \yii\elasticsearch\ElasticsearchTarget
{
    public $includeContext = true;
    public $cacheContext   = true;

    public function setContextMessage($context)
    {
        $this->cacheContext    = true;
        $this->_contextMessage = $context;
    }

    /**
     * @inheritdoc
     */
    public function prepareMessage($message)
    {
        // Convert array and object to JSON to avoid mixed data in index
        if (isset($message[0]) && (is_array($message[0]) || is_object($message[0]))) {
            $message[0]= Json::encode($message[0]);
        }
        return parent::prepareMessage($message);
    }
}
