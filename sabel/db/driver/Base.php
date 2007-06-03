<?php

/**
 * Sabel_DB_Driver_Base
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Driver_Base
{
  protected $connection     = null;
  protected $connectionName = "";

  protected $sql         = "";
  protected $driverId    = "";
  protected $result      = array();
  protected $incrementId = null;

  public function getDriverId()
  {
    return $this->driverId;
  }

  public function setSql($sql)
  {
    $this->sql = $sql;

    return $this;
  }

  public function execute($conn = null)
  {
    $sql = $this->sql;

    // @todo
    //if (defined("QUERY_LOG") && ENVIRONMENT === DEVELOPMENT) {
    if (defined("QUERY_LOG")) {
      var_dump($sql);
    }

    if ($conn === null) {
      $conn = $this->getConnection();
    }

    $func = $this->execFunction;

    switch ($this->driverId) {
      case "mysql":
      case "mysqli":
      case "mssql":
        if (is_array($sql)) {
          foreach ($sql as $s) $func($s, $conn);
          return true;
        } else {
          return $func($sql, $conn);
        }

      case "pgsql":
      case "ibase":
        if (is_array($sql)) {
          foreach ($sql as $s) $func($conn, $s);
          return true;
        } else {
          return $func($conn, $sql);
        }
    }
  }

  public function getResult()
  {
    return $this->result;
  }

  public function getBeforeMethods()
  {
    return array();
  }

  public function getAfterMethods()
  {
    return array();
  }

  public function setConnectionName($connectionName)
  {
    if ($connectionName === $this->connectionName) return;

    if (isset($this->connection)) {
      $this->connection = Sabel_DB_Connection::get($connectionName);
    }

    $this->connectionName = $connectionName;
  }

  public function getConnectionName()
  {
    return $this->connectionName;
  }

  public function getConnection()
  {
    if ($this->connection === null) {
      $conn = Sabel_DB_Connection::get($this->connectionName);
      return $this->connection = $conn;
    } else {
      return $this->connection;
    }
  }

  public function getSqlClass($model, $classType = null)
  {
    if ($classType === null) {
      $classType = Sabel_DB_Sql_Loader::COMMON;
    }

    return Sabel_DB_Sql_Loader::getClass($model, $classType);
  }

  public function getConstraintSqlClass($classType = null)
  {
    if ($classType === null) {
      $classType = Sabel_DB_Sql_Constraint_Loader::COMMON;
    }

    return Sabel_DB_Sql_Constraint_Loader::getClass($classType);
  }

  public function getConditionBuilder($classType = null)
  {
    if ($classType === null) {
      $classType = Sabel_DB_Condition_Builder_Loader::COMMON;
    }

    return Sabel_DB_Condition_Builder_Loader::getClass($this, $classType);
  }

  public function loadTransaction()
  {
    return Sabel_DB_Transaction_Common::getInstance();
  }

  public function begin($connectionName)
  {
    $trans = $this->loadTransaction();

    if (!$trans->isActive($connectionName)) {
      $connection = Sabel_DB_Connection::get($connectionName);
      $this->setSql($this->beginCommand)->execute($connection);
      $trans->start($connection, $this);
    }
  }

  public function commit($connection)
  {
    $this->setSql($this->commitCommand)->execute($connection);
  }

  public function rollback($connection)
  {
    $this->setSql($this->rollbackCommand)->execute($connection);
  }

  public function close($connection)
  {
    $method = $this->closeFunction;
    $method($connection);
  }

  protected function error($error, $sql = null, $pdoBind = null)
  {
    $message = array();
    $name    = $this->getConnectionName();
    $params  = Sabel_DB_Config::get($name);

    if ($sql === null) $sql = $this->sql;

    $message["ERROR_MESSAGE"] = $error;
    $message["EXECUTE_QUERY"] = $sql;

    if ($pdoBind) {
      $message["PDO_BIND_VALUES"] = $pdoBind;
    }

    $message["CONNECTION_NAME"] = $name;
    $message["PARAMETERS"]      = $params;

    throw new Sabel_DB_Exception(print_r($message, true));
  }
}

function escapeString($db, $values, $escMethod = null)
{
  if (!is_array($values)) $values = (array)$values;

  foreach ($values as &$val) {
    if (is_bool($val)) {
      switch ($db) {
        case "mysql":
        case "ibase":
          $val = ($val) ? 1 : 0;
          break;

        case "pgsql":
        case "sqlite":
          $val = ($val) ? "true" : "false";
          break;

        case "mssql":
          $val = ($val) ? "'true'" : "'false'";
          break;
      }
    } elseif (is_string($val) && $escMethod !== null) {
      $val = "'" . $escMethod($val) . "'";
    }
  }

  if (isset($values[0]) && count($values) === 1) {
    return $values[0];
  } else {
    return $values;
  }
}
