<?php

namespace macfly\streamlog;

use Yii;

class ElasticsearchTarget extends \yii\elasticsearch\ElasticsearchTarget
{
    public $includeContext = true;
    public $cacheContext = true;
    /** Format of the date to add after index name, if null no date will be added */
    public $indexDateFormat = 'y-MM-dd';

    private $_index;

    public function init()
    {
        parent::init();
        $this->_index = $this->index;
    }

    public function setContextMessage($context)
    {
        $this->cacheContext    = true;
        $this->_contextMessage = $context;
    }

    protected function setIndex()
    {
        $this->index=sprintf("%s%s", $this->_index, $this->indexDateFormat === null ? '' : '-' . Yii::$app->formatter->asDate(time(), $this->indexDateFormat));
    }

    /**
     * @inheritdoc
     */
    public function export()
    {
        $this->setIndex();
        return parent::export();
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
