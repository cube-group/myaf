<?php

namespace libs\Orm;

/**
 * Class LActiveQuery
 * @author chenqionghe
 * @package libs\Orm
 */
class LActiveQuery extends LDBKernel
{
    /**
     * 模型类
     * @var
     */
    protected $modelClass;

    /**
     * 是否返回数组
     * @var
     */
    protected $asArray;

    /**
     * 设置模型类
     *
     * @param $modelClass
     * @return $this
     */
    public function setModelClass($modelClass)
    {
        $this->modelClass = $modelClass;
        return $this;
    }

    /**
     * 返回数组
     *
     * @param bool $value
     * @return $this
     */
    public function asArray($value = true)
    {
        $this->asArray = $value;
        return $this;
    }

    /**
     * @param string $options
     * @return array|LActiveRecord[]|null
     */
    public function select($options = '')
    {
        $rows = parent::select($options);
        if (!empty($rows)) {
            $models = $this->createModels($rows);
            return $models;
        } else {
            return null;
        }
    }

    /**
     * 创建AR对象
     *
     * @param array $rows
     * @return array|LActiveRecord[]
     */
    private function createModels($rows)
    {
        if ($this->asArray) {
            return $rows;
        }
        /** @var LActiveRecord $model */
        $models = [];
        $class = $this->modelClass;
        foreach ($rows as $row) {
            $model = new $class($this->getDb());
            $model->populateRecord($row);
            $models[] = $model;
        }
        return $models;
    }

}