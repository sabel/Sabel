<?php

/**
 * Sabel_DB_Driver_Base
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Driver_Base
{
  protected $sql      = "";
  protected $driverId = "";
  protected $result   = array();

  protected $connection     = null;
  protected $connectionName = "";

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

  public function loadTransaction()
  {
    return Sabel_DB_Transaction_Common::getInstance();
  }

  protected function error($error)
  {
    $e = new Sabel_DB_Exception_Driver();
    throw $e->exception($this->sql, $error, $this->connectionName);
  }
}

function escapeString($db, $values, $escMethod = null)
{
  if ($values === null) {
    $values = array("");
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

  if (isset($values[0]) && count($values) === 1) {
    return $values[0];
  } else {
    return $values;
  }
}
