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
}
