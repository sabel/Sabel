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
  protected $connection     = null;
  protected $connectionName = "";

  abstract public function getDriverId();
  abstract public function escape($values);
  abstract public function execute(Sabel_DB_Abstract_Statement $stmt);
  abstract public function loadTransaction();
  abstract public function getLastInsertId();
  abstract public function begin($connectionName = null);

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
    if (empty($bindParam)) {
      return $sql;
    } else {
      return str_replace(array_keys($bindParam), $bindParam, $sql);
    }
  }

  public function createSelectSql(Sabel_DB_Abstract_Statement $stmt)
  {
    $sql = "SELECT " . $stmt->getProjection() . " FROM " . $stmt->getTable()
         . $stmt->getJoin() . $stmt->getWhere();

    return $sql . $this->createConstraintSql($stmt->getConstraints());
  }

  public function createInsertSql(Sabel_DB_Abstract_Statement $stmt)
  {
    $binds = array();
    $keys  = array_keys($stmt->getValues());

    foreach ($keys as $key) $binds[] = ":" . $key;

    $sql = array("INSERT INTO " . $stmt->getTable() . " (");
    $sql[] = join(", ", $keys);
    $sql[] = ") VALUES(";
    $sql[] = join(", ", $binds);
    $sql[] = ")";

    return implode("", $sql);
  }

  public function createUpdateSql(Sabel_DB_Abstract_Statement $stmt)
  {
    $tblName = $stmt->getTable();
    $where   = $stmt->getWhere();

    $updates = array();
    foreach ($stmt->getValues() as $column => $value) {
      $updates[] = "$column = :{$column}";
    }

    return "UPDATE $tblName SET " . implode(", ", $updates) . $where;
  }

  public function createDeleteSql(Sabel_DB_Abstract_Statement $stmt)
  {
    return "DELETE FROM " . $stmt->getTable() . $stmt->getWhere();
  }

  protected function createConstraintSql($constraints)
  {
    $sql = "";

    if (isset($constraints["group"]))  $sql .= " GROUP BY " . $constraints["group"];
    if (isset($constraints["having"])) $sql .= " HAVING "   . $constraints["having"];
    if (isset($constraints["order"]))  $sql .= " ORDER BY " . $constraints["order"];

    if (isset($constraints["offset"]) && !isset($constraints["limit"])) {
      $sql .= " LIMIT 100 OFFSET " . $constraints["offset"];
    } else {
      if (isset($constraints["limit"]))  $sql .= " LIMIT "  . $constraints["limit"];
      if (isset($constraints["offset"])) $sql .= " OFFSET " . $constraints["offset"];
    }

    return $sql;
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
