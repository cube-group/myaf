<?php

namespace Base\Orm;

use ArrayAccess;
use Exception;
use IteratorAggregate;
use JsonSerializable;
use Base\Utils\Arrays;
use Serializable;
use Countable;

/**
 * Class LActiveRecord
 * @author chenqionghe
 * @package Base\Orm
 */
abstract class LActiveRecord implements ArrayAccess, IteratorAggregate, JsonSerializable, Serializable, Countable
{
    use ArrayTrait;

    /**
     * 表字段详情信息
     *
     * @var array
     */
    public static $tableColumns = [];

    /**
     * 主键
     * @var array
     */
    public static $primaryKey = [];

    /**
     * 数据库连接
     *
     * @var LDB
     */
    protected $_db;

    /**
     * 旧属性
     *
     * @var array
     */
    private $_oldAttributes = [];

    /**
     * 真实表名
     * @var
     */
    private $trueTableName;

    /**
     * AR constructor.
     */
    public function __construct()
    {
        $this->_db = $this->database();
        $this->setTrueTableName();
    }

    /**
     * 表名
     *
     * @return mixed
     */
    abstract function tableName();

    /**
     * 数据连接LDB
     * @return LDB
     */
    abstract function database();

    /**
     * 获取数据库连接实例
     * @return LDB|mixed
     */
    public function getDb()
    {
        return $this->_db;
    }

    /**
     * 创建查询对象
     *
     * @return LActiveQuery
     */
    public function find()
    {
        $activeQuery = new LActiveQuery($this->_db, $this->trueTableName());
        $activeQuery->setModelClass(get_called_class());
        return $activeQuery;
    }

    /**
     * 保存
     * @param null $attributeNames
     * @return bool
     */
    public function save($attributeNames = null)
    {
        if ($this->isNewRecord()) {
            return $this->insert($attributeNames);
        } else {
            return $this->update($attributeNames) !== false;
        }
    }

    /**
     * 插入
     *
     * @param null $attributes
     * @return bool
     */
    public function insert($attributes = null)
    {
        if (!$this->beforeSave(true)) {
            return false;
        }
        $values = $this->getDirtyAttributes($attributes);
        if ($this->_db->table($this->tableName())->insert($values) === false) {
            return false;
        }
        foreach (static::$primaryKey as $name) {
            $value = $this->_db->lastInsertId($name);
            $id = $this->typeConvert($this->getTableColumn($name), $value);
            $this->setAttribute($name, $id);
        }
        $changedAttributes = array_fill_keys(array_keys($values), null);
        $this->setOldAttributes($values);
        $this->afterSave(true, $changedAttributes);
        return true;
    }

    /**
     * 更新
     *
     * @param null $attributeNames
     * @return mixed
     * @throws Exception
     */
    public function update($attributeNames = null)
    {
        if (!$this->beforeSave(false)) {
            return false;
        }
        $values = $this->getDirtyAttributes($attributeNames);
        $condition = $this->getOldPrimaryKey(true);
        $rows = $this->_db->table($this->tableName())->where($condition)->update($values);
        $changedAttributes = [];
        foreach ($values as $name => $value) {
            $changedAttributes[$name] = isset($this->_oldAttributes[$name]) ? $this->_oldAttributes[$name] : null;
            $this->_oldAttributes[$name] = $value;
        }
        $this->afterSave(false, $changedAttributes);
        return $rows;
    }

    /**
     * 前置钩子
     *
     * @param bool $insert 是否是新增
     * @return bool
     */
    public function beforeSave($insert)
    {
        return true;
    }

    /**
     * 后置钩子
     *
     * @param bool $insert 是否是新增
     * @param $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {

    }

    /**
     * 获取所有的字段属性
     *
     * @return array
     */
    public function attributes()
    {
        return array_keys(static::getTableColumns());
    }

    /**
     * 准备AR对象, 填充属性
     *
     * @inheritdoc
     */
    public function populateRecord($row)
    {
        $tableColumns = static::getTableColumns();
        foreach ($row as $name => $value) {
            if (isset($tableColumns[$name])) {
                $row[$name] = $this->typeConvert($tableColumns[$name], $value);
            }
        }
        $columns = array_flip($this->attributes());
        foreach ($row as $name => $value) {
            if (isset($columns[$name])) {
                $this->_attributes[$name] = $value;
            }
        }
        $this->_oldAttributes = $this->_attributes;

    }

    /**
     * @param $name
     * @return bool
     */
    public function hasAttribute($name)
    {
        return isset($this->_attributes[$name]) || in_array($name, $this->attributes());
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function getAttribute($name)
    {
        return Arrays::get($this->_attributes, $name);
    }

    /**
     * 单个属性赋值
     *
     * @param $name
     * @param $value
     * @throws Exception
     */
    public function setAttribute($name, $value)
    {
        if ($this->hasAttribute($name)) {
            $this->_attributes[$name] = $value;
        } else {
            throw new Exception(get_class($this) . ' has no attribute named "' . $name . '".');
        }
    }

    /**
     * 批量赋值
     *
     * @param $values
     */
    public function setAttributes($values)
    {
        if (is_array($values)) {
            $attributes = array_flip($this->attributes());
            foreach ($values as $name => $value) {
                if (isset($attributes[$name])) {
                    $this->$name = $value;
                }
            }
        }
    }

    /**
     * 获取旧属性
     *
     * @return array
     */
    public function getOldAttributes()
    {
        return $this->_oldAttributes === null ? [] : $this->_oldAttributes;
    }

    /**
     * 设置旧属性
     *
     * @param $values
     */
    public function setOldAttributes($values)
    {
        $this->_oldAttributes = $values;
    }

    /**
     * 获取所有修改的属性
     *
     * @param null $names
     * @return array
     */
    public function getDirtyAttributes($names = null)
    {
        if ($names === null) {
            $names = $this->attributes();
        }
        $names = array_flip($names);
        $attributes = [];
        if ($this->_oldAttributes === null) {
            foreach ($this->_attributes as $name => $value) {
                if (isset($names[$name])) {
                    $attributes[$name] = $value;
                }
            }
        } else {
            foreach ($this->_attributes as $name => $value) {
                if (isset($names[$name]) && (!array_key_exists($name, $this->_oldAttributes) || $value !== $this->_oldAttributes[$name])) {
                    $attributes[$name] = $value;
                }
            }
        }
        return $attributes;
    }

    /**
     * 判断是否是新记录
     *
     * @return bool
     */
    public function isNewRecord()
    {
        return empty($this->_oldAttributes);
    }

    /**
     * 设置是否是新记录
     *
     * @param $value
     */
    public function setNewRecord($value)
    {
        $this->_oldAttributes = $value ? null : $this->_attributes;
    }

    /**
     * 获取主键
     *
     * @param bool $asArray
     * @return array|mixed|null
     * @throws Exception
     */
    public function getOldPrimaryKey($asArray = false)
    {
        $keys = self::$primaryKey;
        if (empty($keys)) {
            throw new Exception(get_class($this) . ' does not have a primary key. You should either define a primary key for the corresponding table or override the primaryKey() method.');
        }
        if (!$asArray && count($keys) === 1) {
            return isset($this->_oldAttributes[$keys[0]]) ? $this->_oldAttributes[$keys[0]] : null;
        } else {
            $values = [];
            foreach ($keys as $name) {
                $values[$name] = isset($this->_oldAttributes[$name]) ? $this->_oldAttributes[$name] : null;
            }

            return $values;
        }
    }

    /**
     * 获取数据库对应php类型
     *
     * @param $column
     * @return mixed|string
     */
    protected function getColumnPhpType($column)
    {
        $typeMap = [
            'smallint' => 'integer',
            'integer' => 'integer',
            'bigint' => 'integer',
            'boolean' => 'boolean',
            'float' => 'double',
            'double' => 'double',
            'binary' => 'resource',
        ];
        if (isset($typeMap[$column['type']])) {
            if ($column['type'] === 'bigint') {
                return PHP_INT_SIZE === 8 && !$column['unsigned'] ? 'integer' : 'string';
            } elseif ($column['type'] === 'integer') {
                return PHP_INT_SIZE === 4 && $column['unsigned'] ? 'string' : 'integer';
            } else {
                return $typeMap[$column['type']];
            }
        } else {
            return 'string';
        }
    }

    /**
     * 获取单个字段信息
     *
     * @param $name
     * @return mixed
     */
    public function getTableColumn($name)
    {
        $tableColumns = static::getTableColumns();
        if (isset($tableColumns[$name])) {
            return $tableColumns[$name];
        }
    }

    /**
     * 获取所有字段信息
     *
     * @return array
     */
    public function getTableColumns()
    {
        if (empty(static::$tableColumns)) {
            $this->loadTableColumns();
        }
        return static::$tableColumns;
    }

    /**
     * @return bool
     * @throws Exception
     */
    protected function loadTableColumns()
    {
        $sql = 'SHOW FULL COLUMNS FROM ' . $this->trueTableName();
        try {
            $columns = $this->find()->getDb()->query($sql);
        } catch (Exception $e) {
            $previous = $e->getPrevious();
            if ($previous instanceof \PDOException && strpos($previous->getMessage(), 'SQLSTATE[42S02') !== false) {
                return false;
            }
            throw $e;
        }
        $columns = Arrays::keyToCase($columns);
        foreach ($columns as $column) {
            if ($column['key'] == "PRI") {
                static::$primaryKey[] = $column['field'];
            }
            static::$tableColumns[$column['field']] = $this->createColumnSchema($column);
        }
    }

    /**
     * 创建数据加字段信息
     *
     * @param $info
     * @return mixed
     */
    protected function createColumnSchema($info)
    {
        $typeMap = [
            'tinyint' => 'smallint',
            'bit' => 'integer',
            'smallint' => 'smallint',
            'mediumint' => 'integer',
            'int' => 'integer',
            'integer' => 'integer',
            'bigint' => 'bigint',
            'float' => 'float',
            'double' => 'double',
            'real' => 'float',
            'decimal' => 'decimal',
            'numeric' => 'decimal',
            'tinytext' => 'text',
            'mediumtext' => 'text',
            'longtext' => 'text',
            'longblob' => 'binary',
            'blob' => 'binary',
            'text' => 'text',
            'varchar' => 'string',
            'string' => 'string',
            'char' => 'string',
            'datetime' => 'datetime',
            'year' => 'date',
            'date' => 'date',
            'time' => 'time',
            'timestamp' => 'timestamp',
            'enum' => 'string',
        ];

        $column['name'] = $info['field'];
        $column['allowNull'] = $info['null'] === 'YES';
        $column['isPrimaryKey'] = strpos($info['key'], 'PRI') !== false;
        $column['autoIncrement'] = stripos($info['extra'], 'auto_increment') !== false;
        $column['comment'] = $info['comment'];
        $column['dbType'] = $info['type'];
        $column['unsigned'] = stripos($column['dbType'], 'unsigned') !== false;
        $column['type'] = 'string';

        if (preg_match('/^(\w+)(?:\(([^\)]+)\))?/', $column['dbType'], $matches)) {
            $type = strtolower($matches[1]);
            if (isset($typeMap[$type])) {
                $column['type'] = $typeMap[$type];
            }
            if (!empty($matches[2])) {
                if ($type === 'enum') {
                    $values = explode(',', $matches[2]);
                    foreach ($values as $i => $value) {
                        $values[$i] = trim($value, "'");
                    }
                    $column['enumValues'] = $values;
                } else {
                    $values = explode(',', $matches[2]);
                    $column['size'] = $column['precision'] = (int)$values[0];
                    if (isset($values[1])) {
                        $column['scale'] = (int)$values[1];
                    }
                    if ($column['size'] === 1 && $type === 'bit') {
                        $column['type'] = 'boolean';
                    } elseif ($type === 'bit') {
                        if ($column['size'] > 32) {
                            $column['type'] = 'bigint';
                        } elseif ($column['size'] === 32) {
                            $column['type'] = 'integer';
                        }
                    }
                }
            }
        }
        $column['phpType'] = $this->getColumnPhpType($column);
        if (!$column['isPrimaryKey']) {
            if ($column['type'] === 'timestamp' && $info['default'] === 'CURRENT_TIMESTAMP') {
                $column['defaultValue'] = $info['default'];
            } elseif (isset($type) && $type === 'bit') {
                $column['defaultValue'] = bindec(trim($info['default'], 'b\''));
            } else {
                $column['defaultValue'] = $this->typeConvert($column, $info['default']);
            }
        }

        return $column;
    }

    /**
     * 转换数据库字段类型为相应php类型
     *
     * @param $column
     * @param $value
     * @return bool|float|int|mixed|null|string
     */
    protected function typeConvert($column, $value)
    {
        if ($value === '' && $column['type'] !== 'text' && $column['type'] !== 'string' && $column['type'] !== 'binary') {
            return null;
        }
        if ($value === null || gettype($value) === $column['phpType']) {
            return $value;
        }
        switch ($column['phpType']) {
            case 'resource':
            case 'string':
                if (is_resource($value)) {
                    return $value;
                }
                if (is_float($value)) {
                    return str_replace(',', '.', (string)$value);
                }
                return (string)$value;
            case 'integer':
                return (int)$value;
            case 'boolean':
                return (bool)$value && $value !== "\0";
            case 'double':
                return (double)$value;
        }
        return $value;
    }

    /**
     * 析构, 销毁db连接
     */
    public function __destruct()
    {
        $this->_db = null;
    }


    /**
     * 返回最近一次执行sql的错误.
     */
    public function lastError()
    {
        $this->_db->lastError();
    }

    /**
     * 获取真实表名
     *
     * @return mixed
     */
    public function trueTableName()
    {
        return $this->trueTableName;
    }

    /**
     * 设置真实表名, 判断没有表前缀就加上
     */
    private function setTrueTableName()
    {
        $this->trueTableName = $this->tableName();
        $option = $this->_db->getOptions();
        if ($prefix = Arrays::get($option, 'prefix')) {
            if ($prefix != substr($this->trueTableName, 0, strlen($prefix))) {
                $this->trueTableName = $prefix . $this->trueTableName;
            }
        }
    }


}
