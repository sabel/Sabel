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
  protected $driverId       = "";
  protected $connection     = null;
  protected $connectionName = "";

  abstract public function escape($values);
  abstract public function execute($sql, $bindParam = null);
  abstract public function loadConstraintSqlClass();
  abstract public function loadTransaction();
  abstract public function begin($connectionName = null);

  public function getDriverId()
  {
    return $this->driverId;
  }

  public function getBeforeMethods()
  {
    return array();
  }

  public function getAfterMethods()
  {
    return array();
  }

  public function getSequenceId(Sabel_DB_Model $model)
  {
    return null;
  }

  public function getLastInsertId(Sabel_DB_Model $model)
  {
    return null;
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

  protected function bind($sql, $bindParam)
  {
    if (!empty($bindParam)) {
      return str_replace(array_keys($bindParam), $bindParam, $sql);
    } else {
      return $sql;
    }
  }
}

// @todo
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
        case "oci":
          $val = ($val) ? 1 : 0;
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
