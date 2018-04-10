<?php
/**
 * Created by PhpStorm.
 * User: linyang
 * Date: 16/8/26
 * Time: 下午10:54
 */

namespace Base\Orm;

use \PDO;
use \Exception;
use \PDOException;

/**
 * Class LDB
 * @package libs\Orm
 */
class LDB
{
    /**
     * 主库配置.
     * array(
     *  'type'=>'mysql',
     *  'host'=>'127.0.0.1',
     *  'port'=>3306,
     *  'username'=>'root',
     *  'password'=>'',
     *  'database'=>'sys',
     *  'charset'=>'uft8',
     *  'prefix'=>'Orm prefix such as google_x_',
     * )
     * @var
     */
    private $options;
    /**
     * 从库配置
     * @var array
     */
    private $optionsSlave;
    /**
     * 主库pdo connection instance.
     * @var \PDO
     */
    private $pdo;
    /**
     * 从库pdo connection instance.
     * @var \PDO
     */
    private $pdoSlave;
    /**
     * 是否强制是用主库连接
     * @var bool
     */
    private $forceUseMaster;

    /**
     * @var int 当前事务个数
     */
    private $transactionNum = 0;
    /**
     * 最近一次执行数据库操作的sql语句.
     * @var array
     */
    private $allSql = [];
    /**
     * 最近一次执行数据库操作的错误.
     * @var array
     */
    private $allError = [];


    /**
     * 工厂模式生成数据库操作连接实例.
     * @param $options
     * @return LDB
     */
    public static function create($options, $optionsSlave = null)
    {
        return new self($options, $optionsSlave);
    }

    /**
     * LDB constructor.
     * @param $options array 主库配置
     * @param null $optionsSlave 从库配置
     * @throws Exception
     */
    public function __construct($options, $optionsSlave = null)
    {
        //extension check.
        if (!extension_loaded('pdo_mysql')) {
            throw new Exception('Ext pdo_mysql is not exist!');
        }
        if ($options) {
            $options['type'] = isset($options['type']) ? $options['type'] : 'mysql';
        }
        if ($optionsSlave) {
            $optionsSlave['type'] = isset($optionsSlave['type']) ? $optionsSlave['type'] : 'mysql';
        }

        $this->options = $options;
        $this->optionsSlave = $optionsSlave;
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        $this->close();
    }


    /**
     * 获取pdo连接实例.
     *
     * @param bool $isWrite 是否为写库连接(即主库)
     * @return bool|\PDO
     */
    private function getConnection($isWrite = false)
    {
        $master = $this->forceUseMaster || $isWrite || (!$isWrite && !$this->optionsSlave);

        $pdo = null;
        $options = null;
        if ($master) {
            if ($this->pdo) {
                $pdo = $this->pdo;
            } else {
                $options = $this->options;
            }
        } else {
            if ($this->pdoSlave) {
                $pdo = $this->pdoSlave;
            } else {
                $options = $this->optionsSlave;
            }
        }

        if (!$pdo) {
            try {
                $pdo = new PDO(
                    $options['type'] . ':host=' . $options['host'] . ';port=' . $options['port'] . ';dbname=' . $options['database'],
                    $options['username'],
                    $options['password'],
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
//            $pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
                if (isset($options['charset'])) {
                    $pdo->exec('SET NAMES ' . $options['charset'] . ';');
                }
                if ($master) {
                    $this->pdo = $pdo;
                } else {
                    $this->pdoSlave = $pdo;
                }
            } catch (PDOException $e) {
                $this->allError[] = $e->getMessage();
                return false;
            } catch (Exception $e) {
                $this->allError[] = $e->getMessage();
                return false;
            }
        }

        return $pdo;
    }

    /**
     * 返回最近一次执行sql的错误.
     * @return string
     */
    public function lastError()
    {
        return $this->allError ? $this->allError[0] : '';
    }

    /**
     * 返回最近一次执行的sql语句.
     * @return string
     */
    public function lastSql()
    {
        return $this->allSql ? $this->allSql[0] : '';
    }


    /**
     * 返回所有执行sql过程中的error.
     * @return array
     */
    public function allError()
    {
        return $this->allError;
    }

    /**
     * 返回所有执行的sql语句.
     * @return array
     */
    public function allSql()
    {
        return $this->allSql;
    }

    /**
     * get last insert id.
     * @param string $name 主键名
     * @return mixed
     */
    public function lastInsertId($name = null)
    {
        if ($pdo = $this->getConnection(true)) {
            try {
                return $pdo->lastInsertId($name);
            } catch (PDOException $e) {
                array_unshift($this->allError, $e->getMessage());
            } catch (Exception $e) {
                array_unshift($this->allError, $e->getMessage());
            }
        }
        return false;
    }


    /**
     * create LDBKernel instance.
     *
     * $db = new DB($options);
     * $db->table('list');
     *
     * @param $name string table name(not contains table prefix)
     * @param $forceUseMaster bool 是否强制使用主库
     * @return LDBKernel|LDBNull
     */
    public function table($name, $forceUseMaster = false)
    {
        $this->forceUseMaster = $forceUseMaster;
        if ($this->options) {
            return new LDBKernel($this, !isset($this->options['prefix']) ? $name : ($this->options['prefix'] . $name));
        }
        return new LDBNull();
    }

    /**
     * execute sql.
     * (no collection returned)
     * for update/delete/insert.
     *
     * @param $sql string
     * @param $params array|null 支持prepare
     * @return int|bool
     */
    public function exec($sql, $params = null)
    {
        if ($pdo = $this->getConnection(true)) {
            try {
                array_unshift($this->allSql, $sql);
                $result = false;
                if ($params) {
                    $st = $pdo->prepare($sql);
                    if ($st->execute($params)) {
                        $result = $st->rowCount();
                    }
                } else {
                    $result = $pdo->exec($sql);
                }
                return $result;
            } catch (PDOException $e) {
                array_unshift($this->allError, $e->getMessage());
            } catch (Exception $e) {
                array_unshift($this->allError, $e->getMessage());
            }
        }
        return false;
    }


    /**
     * execute sql.
     * (collection returned)
     *
     * @param $sql string
     * @param $params array|null 支持prepare
     * @return mixed
     */
    public function query($sql, $params = null)
    {
        if ($pdo = $this->getConnection()) {
            try {
                array_unshift($this->allSql, $sql);
                $result = false;
                if ($params) {
                    $st = $pdo->prepare($sql);
                    $st->execute($params);
                } else {
                    $st = $pdo->query($sql);
                }
                if ($st) {
                    $result = $st->fetchAll(PDO::FETCH_ASSOC);
                }
                return $result;
            } catch (PDOException $e) {
                array_unshift($this->allError, $e->getMessage());
            } catch (Exception $e) {
                array_unshift($this->allError, $e->getMessage());
            }
        }
        return false;
    }


    /**
     * Get PDO fetchColumn.
     * @param $sql string
     * @param $params array|null 支持prepare
     * @return null
     */
    public function column($sql, $params = null)
    {
        if ($pdo = $this->getConnection()) {
            try {
                array_unshift($this->allSql, $sql);
                $result = false;
                if ($params) {
                    $st = $pdo->prepare($sql);
                    $st->execute($params);
                } else {
                    $st = $pdo->query($sql);
                }
                if ($st) {
                    $result = $st->fetchColumn();
                }
                return $result;
            } catch (PDOException $e) {
                array_unshift($this->allError, $e->getMessage());
            } catch (Exception $e) {
                array_unshift($this->allError, $e->getMessage());
            }
        }
        return false;
    }


    /**
     * Task start.
     * @return bool
     */
    public function beginTransaction()
    {
        ++$this->transactionNum;
        $this->getConnection(true);
        if ($this->pdo && $this->transactionNum == 1) {
            return $this->pdo->beginTransaction();
        } else {
            return $this->createSavepoint($this->getSavepointName());
        }
    }


    /**
     * Task end.
     * @return bool
     */
    public function commit()
    {
        if ($this->pdo) {
            try {
                if ($this->transactionNum == 1) {
                    $result = $this->pdo->commit();
                } else {
                    $result = $this->releaseSavepoint($this->getSavepointName());
                }
                --$this->transactionNum;
                return $result;
            } catch (PDOException $e) {
                array_unshift($this->allError, $e->getMessage());
            } catch (Exception $e) {
                array_unshift($this->allError, $e->getMessage());
            }
        }
        return false;
    }

    /**
     * Task force rollback.
     * @return bool
     */
    public function rollBack()
    {
        if ($this->pdo) {
            if ($this->transactionNum == 1) {
                $result = $this->pdo->rollBack();
            } else {
                $result = $this->rollBackSavepoint($this->getSavepointName());
            }
            --$this->transactionNum;
            return $result;
        }
        return false;
    }


    /**
     * Close the Orm instance.
     * @return bool
     */
    public function close()
    {
        $this->allSql = null;
        $this->allError = null;
        $this->pdo = null;
        $this->pdoSlave = null;

        return true;
    }


    /**
     * 获取回滚点名称
     *
     * @return string
     */
    public function getSavepointName()
    {
        return 'LEVEL' . $this->transactionNum;
    }

    /**
     * 创建回滚点
     *
     * @param $name
     * @return bool
     */
    public function createSavepoint($name)
    {
        return false !== $this->exec("SAVEPOINT $name");
    }

    /**
     * 回滚指定回滚点
     *
     * @param $name
     * @return bool
     */
    public function rollBackSavepoint($name)
    {
        return false !== $this->exec("ROLLBACK TO SAVEPOINT $name");
    }


    /**
     * 释放回滚点
     *
     * @param $name
     * @return bool
     */
    public function releaseSavepoint($name)
    {
        return false !== $this->exec("RELEASE SAVEPOINT $name");
    }

    /**
     * @return mixed
     */
    public function getOptions()
    {
        return $this->options;
    }

}