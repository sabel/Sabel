<?php

/**
 * Sabel_DB_Abstract_Driver
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Abstract_Driver extends Sabel_Object
{
  const TRANS_ISOLATION_READ_UNCOMMITTED = 1;
  const TRANS_ISOLATION_READ_COMMITTED   = 2;
  const TRANS_ISOLATION_REPEATABLE_READ  = 3;
  const TRANS_ISOLATION_SERIALIZABLE     = 4;
  
  protected
    $autoCommit = true,
    $connection = null,
    $connectionName = "";
    
  abstract public function getDriverId();
  abstract public function connect(array $params);
  abstract public function begin($isolationLevel = null);
  abstract public function commit();
  abstract public function rollback();
  abstract public function execute($sql, $bindParams = null);
  abstract public function getLastInsertId();
  abstract public function close($connection);
  
  public function __construct($connectionName)
  {
    $this->connectionName = $connectionName;
  }
  
  public function setConnection($connection)
  {
    $this->connection = $connection;
  }
  
  public function getConnection()
  {
    return $this->connection;
  }
  
  public function autoCommit($bool)
  {
    $this->autoCommit = $bool;
  }
  
  public function getConnectionName()
  {
    return $this->connectionName;
  }
  
  public function setTransactionIsolationLevel($level)
  {
    switch ($level) {
      case self::TRANS_ISOLATION_READ_UNCOMMITTED:
        $query = "SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED";
        break;
      case self::TRANS_ISOLATION_READ_COMMITTED:
        $query = "SET TRANSACTION ISOLATION LEVEL READ COMMITTED";
        break;
      case self::TRANS_ISOLATION_REPEATABLE_READ:
        $query = "SET TRANSACTION ISOLATION LEVEL REPEATABLE READ";
        break;
      case self::TRANS_ISOLATION_SERIALIZABLE:
        $query = "SET TRANSACTION ISOLATION LEVEL SERIALIZABLE";
        break;
      default:
        throw new Sabel_Exception_InvalidArgument("invalid isolation level.");
    }
    
    $this->execute($query);
  }
  
  protected function bind($sql, $bindParam)
  {
    if (empty($bindParam)) return $sql;
    
    if (in_array(null, $bindParam, true)) {
      foreach ($bindParam as $key => $val) {
        $val = ($val === null) ? "NULL" : $val;
        $sql = str_replace($key, $val, $sql);
      }
      return $sql;
    } else {
      return str_replace(array_keys($bindParam), $bindParam, $sql);
    }
  }
}
