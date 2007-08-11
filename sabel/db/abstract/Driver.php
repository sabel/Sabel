<?php

/**
 * Sabel_DB_Abstract_Driver
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Abstract_Driver
{
  protected $sql            = "";
  protected $driverId       = "";
  protected $result         = array();
  protected $connection     = null;
  protected $connectionName = "";

  abstract public function execute();
  abstract public function escape($values);
  abstract public function loadSqlClass($model);
  abstract public function loadConditionBuilder();
  abstract public function loadConstraintSqlClass();
  abstract public function loadTransaction();
  abstract public function begin($connectionName = null);

  public function getDriverId()
  {
    return $this->driverId;
  }

  public function setSql($sql)
  {
    $this->sql = $sql;

    return $this;
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

  public function close($connection)
  {
    $method = $this->closeFunction;
    $method($connection);

    unset($this->connection);
  }

  protected function error($error)
  {
    throw new Sabel_DB_Exception($error);
  }
}

function escapeString($db, $values, $escMethod = null)
{
  if ($values === null) {
    return "''";
  } elseif (!is_array($values)) {
    $values = (array)$values;
  }

  foreach ($values as &$val) {
    if (is_bool($val)) {
      switch ($db) {
        case "mysql":
        case "mysqli":
        case "oci":
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

  return (isset($values[0])) ? $values[0] : $values;
}
