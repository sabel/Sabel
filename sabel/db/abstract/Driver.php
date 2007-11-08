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
abstract class Sabel_DB_Abstract_Driver extends Sabel_Object
{
  protected
    $autoCommit = true,
    $connection = null,
    $connectionName = "";

  protected
    $placeHolderPrefix = "@",
    $placeHolderSuffix = "@";

  abstract public function getDriverId();
  abstract public function escape(array $values);
  abstract public function execute($sql, $bindParams = null);
  abstract public function begin();
  abstract public function commit();
  abstract public function rollback();
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
    if ($this->connection === null) {
      return $this->connection = Sabel_DB_Connection::get($this->connectionName);
    } else {
      return $this->connection;
    }
  }

  public function autoCommit($bool)
  {
    $this->autoCommit = $bool;
  }

  public function getConnectionName()
  {
    return $this->connectionName;
  }

  public function createSelectSql(Sabel_DB_Abstract_Statement $stmt)
  {
    $sql = "SELECT " . $stmt->getProjection() . " FROM " . $stmt->getTable()
         . $stmt->getJoin() . $stmt->getWhere();

    return $sql . $this->createConstraintSql($stmt->getConstraints());
  }

  public function createInsertSql(Sabel_DB_Abstract_Statement $stmt)
  {
    $binds  = array();
    $values = $stmt->getValues();
    $keys   = array_keys($values);
    $prefix = $this->placeHolderPrefix;
    $suffix = $this->placeHolderSuffix;

    foreach ($keys as $key) {
      $binds[] = $prefix . $key . $suffix;
    }

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
    $prefix = $this->placeHolderPrefix;
    $suffix = $this->placeHolderSuffix;

    $updates = array();
    foreach ($stmt->getValues() as $column => $value) {
      $updates[] = "$column = {$prefix}{$column}{$suffix}";
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

  public function getPrefixOfPlaceHelder()
  {
    return $this->placeHolderPrefix;
  }

  public function getSuffixOfPlaceHelder()
  {
    return $this->placeHolderSuffix;
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
