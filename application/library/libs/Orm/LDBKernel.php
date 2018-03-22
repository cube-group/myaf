<?php
/**
 * Created by PhpStorm.
 * User: linyang3
 * Date: 17/2/15
 * Time: 下午2:21
 */

namespace libs\Orm;

/**
 * Class LDBKernel.
 * sql Orm model unit.
 * @package com\cube\Orm
 */
class LDBKernel
{
    /**
     * the name of the table.
     * @var string
     */
    protected $_tableName = '';
    /**
     * main table as name
     * @var string
     */
    protected $_asTable = '';
    /**
     * sql where array.
     * @var array
     */
    protected $_where = [];
    /**
     * sql order string.
     * @var string
     */
    protected $_order = '';
    /**
     * sql group string.
     * @var string
     */
    protected $_group = '';
    /**
     * sql limit string.
     * @var string
     */
    protected $_limit = '';
    /**
     * sql join string.
     * @var string
     */
    protected $_join = '';
    /**
     * sql join string's on string.
     * @var string
     */
    protected $_on = '';
    /**
     * sql insert unique operation.
     * @var string
     */
    protected $_duplicate = '';

    /**
     * latest sql string.
     * @var string
     */
    protected $_sql = '';
    /**
     * @var LDB
     */
    protected $_db = null;


    /**
     * LDBKernel constructor.
     * @param $db LDB
     * @param $tableName string
     */
    public function __construct($db, $tableName)
    {
        $this->_db = $db;
        $this->_tableName = $tableName;
    }

    /**
     * 析构
     */
    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        $this->_where = null;
        $this->_order = '';
        $this->_group = '';
        $this->_limit = '';
        $this->_join = '';
        $this->_on = '';
        $this->_sql = '';
        $this->_duplicate = '';
        $this->_db = null;
    }


    /**
     * @return LDB
     */
    public function getDb()
    {
        return $this->_db;
    }


    /**
     * 获取最近一次执行的sql语句.
     *
     * @return string
     */
    public function getSql()
    {
        return ($this->_sql && is_array($this->_sql)) ? json_encode($this->_sql) : $this->_sql;
    }

    /**
     * 获取完整表名称.
     *
     * @return string
     */
    public function getName()
    {
        return $this->_tableName;
    }

    /**
     * 将主表名称进行重命名
     *
     * @param $name string
     * @return $this
     */
    public function asTable($name)
    {
        $this->_asTable = $name;
        return $this;
    }

    /**
     * where operate.
     *
     * $db->table('list')->where('a=1 and (b=2 or c=3)')->select();
     * SQL:select * from list where a=1 and (b=2 or c=3);
     *
     * @param $options array|string
     * @return $this
     */
    public function where($options)
    {
        $this->_where[0] = self::getWhere($options);
        return $this;
    }

    /**
     * and where operate.
     *
     * $db->table('list')->where('a=1 and (b=2 or c=3)')->andWhere(['bb'=>[1,2,3]])->select();
     * SQL:select * from list where a=1 and (b=2 or c=3) and (bb in (1,2,3));
     *
     * @param $options array|string
     * @return $this
     */
    public function andWhere($options)
    {
        if (!$this->_where) {
            return self::where($options);
        }
        $whereResult = self::getWhere($options);
        if ($whereResult) {
            $this->_where[] = 'AND (' . $whereResult . ')';
        }

        return $this;
    }


    /**
     * or where operate.
     *
     * $db->table('list')->where('a=1 and (b=2 or c=3)')->orWhere(['bb'=>[1,2,3]])->select();
     * SQL:select * from list where a=1 and (b=2 or c=3) or (bb in (1,2,3));
     *
     * @param $options array|string
     * @return $this
     */
    public function orWhere($options)
    {
        if ($this->_where) {
            $whereResult = self::getWhere($options);
            if ($whereResult) {
                $this->_where[] = 'OR (' . $whereResult . ')';
            }
        }

        return $this;
    }

    /**
     * select by the order.
     *
     * $db->table('list')->order('userid ASC')->select();
     * SQL:select * from list order by userid asc;
     *
     * $db->table('list')->order(array('userid ASC','username DESC'))->select();
     * SQL:select * from list order by userid asc,username desc;
     *
     * @param $options array|string|null
     * @return $this
     */
    public function order($options)
    {
        if ($options) {
            if (is_array($options)) {
                $newOptions = [];
                foreach ($options as $item) {
                    $newOptions[] = "`{$item}`";
                }
                $this->_order = join(',', $newOptions);
            } else {
                $this->_order = $options;
            }
        }
        return $this;
    }

    /**
     * select by the group.
     *
     * $db->table('list')->group('userid')->select();
     * SQL:select * from list group by userid;
     *
     * $db->table('list')->group(array('userid','username'))->select();
     * SQL:select * from list group by userid,username;
     *
     * @param $options array|null
     * @return $this
     */
    public function group($options)
    {
        if ($options) {
            if (is_array($options)) {
                $newOptions = [];
                foreach ($options as $item) {
                    $newOptions[] = "`{$item}`";
                }
                $this->_group = join(',', $newOptions);
            } else {
                $this->_group = $options;
            }
        }
        return $this;
    }

    /**
     * limit pages.
     *
     * $db->table('list')->limit(0,10)->select();
     * SQL:select * from list limit 0,10;
     *
     * @param $start
     * @param $length
     * @return $this
     */
    public function limit($start, $length)
    {
        if ($start >= 0 && $length > 0) {
            $this->_limit = $start . ',' . $length;
        }
        return $this;
    }

    /**
     * join table.
     * @param $tableName string
     * @param string $type
     * @return $this
     */
    public function join($tableName, $type = 'INNER')
    {
        if ($tableName) {
            $this->_join = $type . " JOIN {$tableName}";
        }
        return $this;
    }

    /**
     * on sql when join sql exists.
     * @param $value string
     * @return self
     */
    public function on($value)
    {
        $this->_on = $value;
        return $this;
    }

    /**
     * duplicate sql when insert.
     * @param $options string
     * @return self
     */
    public function duplicate($options)
    {
        if ($options && is_array($options)) {
            $sets = [];
            foreach ($options as $column => $value) {
                if (is_string($value)) {
                    $sets[] = $column . "='" . addslashes($value) . "'";
                } else {
                    $sets[] = $column . "=" . $value;
                }
            }
            $this->_duplicate = ' ON DUPLICATE KEY UPDATE ' . join(',', $sets);
        }
        return $this;
    }

    /**
     * get the count of the select.
     *
     * $db->table('list')->count();
     * SQL:select count(*) from list;
     *
     * $db->table('list')->where('a=1')->count();
     * SQL:select count(*) from list where a=1;
     *
     * @param $distinct string
     * @return int
     */
    public function count($distinct = '')
    {
        $sql = 'SELECT COUNT';
        $sql .= $distinct ? "(DISTINCT {$distinct})" : "(*)";
        $sql .= $this->getConditionStatement(__FUNCTION__);

        return $this->returnValue($this->_db->column($sql), $sql);
    }

    /**
     * 查询符合当前sql的和.
     *
     * $db->table('list')->sum('score');
     * SQL:select sum(score) from list;
     *
     * $db->table('list')->where('a=1')->sum('score');
     * SQL:select sum(score) from list where a=1;
     *
     * @param $value
     * @return array|int
     */
    public function sum($value)
    {
        if (!$value) {
            return false;
        }
        $sql = 'SELECT SUM(' . $value . ')';
        $sql .= $this->getConditionStatement(__FUNCTION__);

        return $this->returnValue($this->_db->column($sql), $sql);
    }


    /**
     * select operate and return the first!
     * @param string|array $options
     * @return bool|mixed
     */
    public function one($options = '')
    {
        $result = $this->limit(0, 1)->select($options);
        if ($result && is_array($result) && count($result) > 0) {
            foreach ($result as $row) {
                return $row;
            }
        }
        return false;
    }

    /**
     * select operate.
     *
     * $db->table('list')->select();
     * SQL:select * FROM list;
     *
     * $db->table('list')->select('username');
     * SQL:select username from list;
     *
     * $db->table('list')->select(array('username','team'));
     * SQL:select username,team from list;
     *
     * @param string|array $options
     * @return array
     */
    public function select($options = '')
    {
        $sql = 'SELECT ';
        if ($options) {
            $sql .= (is_array($options) ? ("`" . join('`,`', $options) . "`") : "`{$options}`");
        } else {
            $sql .= '*';
        }
        $sql .= $this->getConditionStatement(__FUNCTION__);
        return $this->returnValue($this->_db->query($sql), $sql);
    }

    /**
     * 高级分页功能.
     *
     * $db->table('list')->page(5);
     * SQL:select * from list limit 40,10;
     *
     * @param int $page
     * @param int $pageSize
     * @return array
     */
    public function page($page = 1, $pageSize = 10)
    {
        if (!$page || !is_numeric($page) || is_nan($page)) {
            $page = 1;
        }
        if (!$pageSize) {
            $pageSize = 10;
        }
        if ($total = $this->count()) {
            $pageTotal = ceil($total / $pageSize);
            if ($page > $pageTotal) {
                $page = $pageTotal;
            }
            return [
                'page' => ['totalCount' => $total, 'totalPage' => $pageTotal, 'currentPage' => $page, 'pageSize' => $pageSize],
                'list' => $this->limit(($page - 1) * $pageSize, $pageSize)->select()
            ];
        }
        return [
            'page' => ['totalCount' => 0, 'totalPage' => 0, 'currentPage' => 1, 'pageSize' => $pageSize],
            'list' => []
        ];
    }

    /**
     * update.
     *
     * $db->table('list')->where('a=1 and b="world"')->update(array('c'=>2,'d'=>'hello'));
     * SQL:update list c=2,d="hello" where a=1 and b="world";
     *
     * $db->table('list as L')->where('U.a=L.d')->update(['U.c'=>2,'d'=>'hello'],'user as U');
     * SQL:update list as L,user as U set U.c=2,U.d="hello" where U.a=L.d;
     *
     * @param $options array
     * @param $tables array|null
     * @return int
     */
    public function update($options, $tables = null)
    {
        if (!$options || !is_array($options)) {
            return false;
        }
        if ($tables && !is_array($tables)) {
            $tables = [$tables];
        }
        if (!$tables) {
            $tables = [];
        }
        array_unshift($tables, $this->_tableName);
        foreach ($tables as $tableKey => $tableItem) {
            $tables[$tableKey] = "`{$tableItem}`";
        }

        $sql = 'UPDATE ' . join(',', $tables) . ' SET ';
        $sets = [];
        foreach ($options as $column => $value) {
            if (is_string($value)) {
                $sets[] = "`{$column}`='" . addslashes($value) . "'";
            } else {
                $sets[] = "`{$column}`=" . $value;
            }
        }
        $sql .= join(',', $sets);
        $sql .= ' WHERE ';
        $sql .= $this->getFinalWhere();

        return $this->returnValue($this->_db->exec($sql), $sql);
    }

    /**
     * delete.
     *
     * $db->table('list')->where('a=1')->delete();
     * SQL:delete from list where a=1;
     *
     * @return int
     */
    public function delete()
    {
        if (!$this->_where) {
            return false;
        }
        $sql = 'DELETE';
        $sql .= $this->getConditionStatement(__FUNCTION__);

        return $this->returnValue($this->_db->exec($sql), $sql);
    }

    /**
     * insert action.
     *
     * $db->table('list')->where('a=1')->insert(array('a'=>1,'c'='2'));
     * SQL:insert into list (a,c) values (1,2);
     * RESULT: index(if index==false error)
     *
     * @param $options array
     * @return int
     */
    public function insert($options)
    {
        if (!$options || !is_array($options) || !count($options)) {
            return false;
        }

        $sql = 'INSERT INTO ' . $this->_tableName;
        $columns = [];
        $values = [];
        foreach ($options as $key => $value) {
            $columns[] = $key;
            if (is_string($value)) {
                $values[] = "'" . addslashes($value) . "'";
            } else {
                $values[] = $value;
            }
        }
        $sql .= ' (`' . join('`,`', $columns) . '`)';
        $sql .= ' VALUES (' . join(',', $values) . ')';
        if ($this->_duplicate) {
            $sql .= $this->_duplicate;
        }
        return $this->returnValue($this->_db->exec($sql), $sql);
    }


    /**
     * insert multiple values.
     *
     * @param $columns array
     * @param $values array
     *
     * @return bool
     */
    public function insertMulti($columns, $values)
    {
        if (!$columns || !$values || !is_array($columns) || !count($columns) || !is_array($values) || !count($values)) {
            return false;
        }

        $sql = 'INSERT INTO ' . $this->_tableName;
        $sql .= ' (`' . join('`,`', $columns) . '`)';
        $sqlArr = [];
        foreach ($values as $item) {
            $valueItem = [];
            foreach ($item as $value) {
                if (is_string($value)) {
                    $valueItem[] = "'" . addslashes($value) . "'";
                } else {
                    $valueItem[] = $value;
                }
            }
            $sqlArr[] = '(' . join(',', $valueItem) . ')';
        }

        $sql .= ' VALUES ' . join(',', $sqlArr);
        return $this->returnValue($this->_db->exec($sql), $sql);
    }

    /**
     * 处理where语句.
     *
     * @param $options array|string
     * @return string
     */
    protected function getWhere($options)
    {
        if (!$options) {
            return '';
        }
        if (is_array($options)) {
            $wheres = [];
            foreach ($options as $column => $value) {
                if (is_string($value)) {
                    $wheres[] = "`{$column}`='" . addslashes($value) . "'";
                } else if (is_array($value)) {
                    $inValueArr = [];
                    foreach ($value as $inValue) {
                        $inValueArr[] = is_string($inValue) ? ("'" . addslashes($inValue) . "'") : $inValue;
                    }
                    $wheres[] = "`{$column}`" . ' IN (' . join(',', $inValueArr) . ')';
                } else {
                    $wheres[] = "`{$column}`=" . $value;
                }
            }
            return join(' AND ', $wheres);
        }
        return $options;
    }


    /**
     * 整理where队列语句.
     * @return string|bool
     */
    protected function getFinalWhere()
    {
        if (!$this->_where) {
            return false;
        }
        return join(' ', $this->_where);
    }


    /**
     * 获取sql条件语句.
     * @param $type string
     * @return string
     */
    protected function getConditionStatement($type)
    {
        $sql = " FROM `{$this->_tableName}`";
        if ($this->_asTable) {
            $sql .= ' AS ' . $this->_asTable;
        }
        if ($this->_join) {
            $sql .= ' ' . $this->_join;
            if ($this->_on) {
                $sql .= ' ON ' . $this->_on;
            }
        }
        if ($where = $this->getFinalWhere()) {
            $sql .= ' WHERE ' . $where;
        }
        if ($this->_group) {
            $sql .= ' GROUP BY ' . $this->_group;
        }
        if ($this->_order) {
            $sql .= ' ORDER BY ' . $this->_order;
        }
        if ($this->_limit) {
            $sql .= ' LIMIT ' . $this->_limit;
        }
        return $sql;
    }

    /**
     * LDBKernel实例的生命周期即将结束.
     * 如果不改变表名还可继续使用该LDBKernel实例
     *
     * @param $value mixed
     * @param $sql string
     * @return mixed
     */
    protected function returnValue($value, $sql = '')
    {
        $this->_where = [];
        $this->_order = '';
        $this->_group = '';
        $this->_limit = '';
        $this->_join = '';
        $this->_on = '';
        $this->_duplicate = '';
        $this->_sql = $sql;

        return $value;
    }
}