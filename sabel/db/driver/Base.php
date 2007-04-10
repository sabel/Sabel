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
    if (defined("QUERY_LOG") && ENVIRONMENT === DEVELOPMENT) {
      var_dump($sql);
    }

    if ($conn === null) {
      if ($this->connection === null) {
        $conn = Sabel_DB_Connection::get($this->connectionName);
        $this->connection = $conn;
      } else {
        $conn = $this->connection;
      }
    }

    $func = $this->execFunction;

    switch ($this->driverId) {
      case "mysql":
      case "mysql41":
      case "mssql":
        if (is_array($sql)) {
          foreach ($sql as $s) $func($s, $conn);
          break;
        } else {
          return $func($sql, $conn);
        }

      case "pgsql":
      case "pgsql81":
      case "ibase":
        if (is_array($sql)) {
          foreach ($sql as $s) $func($s, $conn);
          break;
        } else {
          return $func($conn, $sql);
        }
    }
  }

  public function getResult()
  {
    return $this->result;
  }

  public function getResultSet($command = null)
  {
    if ($command === null) {
      return new Sabel_DB_Result_Row($this->result);
    } else {
      $command->setResult(new Sabel_DB_Result_Row($this->result));
    }
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
    if ($connectionName !== $this->connectionName && isset($this->connection)) {
      $this->connection = Sabel_DB_Connection::get($connectionName);
    }

    $this->connectionName = $connectionName;
  }

  public function getConnectionName()
  {
    return $this->connectionName;
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
}

function escapeString($db, $values, $escMethod = null)
{
  if (!is_array($values)) $values = (array)$values;

  foreach ($values as &$val) {
    if (is_bool($val)) {
      switch ($db) {
        case "pgsql":
        case "mssql":
        case "sqlite":
          $val = ($val) ? 'true' : 'false';
          break;

        case "mysql":
        case "ibase":
          $val = ($val) ? 1 : 0;
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
