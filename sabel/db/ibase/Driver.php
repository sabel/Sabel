<?php

/**
 * Sabel_DB_Ibase_Driver
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Ibase_Driver extends Sabel_DB_Abstract_Driver
{
  private $lastInsertId = null;

  public function getDriverId()
  {
    return "ibase";
  }

  public function begin()
  {
    $this->autoCommit = false;
    $trans = ibase_trans(IBASE_COMMITTED|IBASE_REC_NO_VERSION, $this->connection);
    return $this->connection = $trans;
  }

  public function commit()
  {
    if (ibase_commit($this->connection)) {
      $this->autoCommit = true;
    } else {
      throw new Sabel_DB_Exception("ibase driver commit failed.");
    }
  }

  public function rollback()
  {
    if (ibase_rollback($this->connection)) {
      $this->autoCommit = true;
    } else {
      throw new Sabel_DB_Exception("ibase driver rollback failed.");
    }
  }

  public function close($connection)
  {
    ibase_close($connection);
    unset($this->connection);
  }

  public function escape(array $values)
  {
    foreach ($values as &$val) {
      if (is_bool($val)) {
        $val = ($val) ? 1 : 0;
      } elseif (is_string($val)) {
        $val = "'" . ibase_escape_string($val) . "'";
      }
    }

    return $values;
  }

  public function execute($sql, $bindParams = null)
  {
    if ($bindParams !== null) {
      $sql = $this->bind($sql, $this->escape($bindParams));
    }

    $result = ibase_query($this->connection, $sql);
    if (!$result) $this->executeError($sql);

    $rows = array();
    if (is_resource($result)) {
      while ($row = ibase_fetch_assoc($result, IBASE_TEXT)) {
        $rows[] = array_change_key_case($row);
      }
      ibase_free_result($result);
    }

    if ($this->autoCommit) ibase_commit($this->connection);
    return (empty($rows)) ? null : $rows;
  }

  public function getLastInsertId()
  {
    return $this->lastInsertId;
  }

  public function createSelectSql(Sabel_DB_Abstract_Statement $stmt)
  {
    $sql = "SELECT ";
    $constraints = $stmt->getConstraints();

    if (isset($constraints["limit"])) {
      $query  = "FIRST {$constraints["limit"]} ";
      $query .= (isset($constraints["offset"])) ? "SKIP " . $constraints["offset"] : "SKIP 0";
      $sql   .= $query;
    } elseif (isset($constraints["offset"])) {
      $sql   .= "SKIP " . $constraints["offset"];
    }

    $sql .= " " . $stmt->getProjection() . " FROM ";
    $sql .= $stmt->getTable() . $stmt->getJoin() . $stmt->getWhere();

    return $sql . $this->createConstraintSql($constraints);
  }

  public function createInsertSql(Sabel_DB_Abstract_Statement $stmt)
  {
    // @todo refactoring

    $binds   = array();
    $tblName = $stmt->getTable();
    $keys    = array_keys($stmt->getValues());
    $prefix  = $this->placeHolderPrefix;
    $suffix  = $this->placeHolderSuffix;

    if (($column = $stmt->getSequenceColumn()) !== null) {
      $keys[] = $column;
      $genName = strtoupper("{$tblName}_{$column}_gen");
      $this->lastInsertId = ibase_gen_id($genName, 1, $this->connection);
      $stmt->setBindValue($column, $this->lastInsertId);
    }

    foreach ($keys as $key) {
      $binds[] = $prefix . $key . $suffix;
    }

    $sql = array("INSERT INTO $tblName (");
    $sql[] = join(", ", $keys);
    $sql[] = ") VALUES(";
    $sql[] = join(", ", $binds);
    $sql[] = ")";

    return implode("", $sql);
  }

  protected function createConstraintSql($constraints)
  {
    $sql = "";

    if (isset($constraints["group"]))  $sql .= " GROUP BY " . $constraints["group"];
    if (isset($constraints["having"])) $sql .= " HAVING "   . $constraints["having"];
    if (isset($constraints["order"]))  $sql .= " ORDER BY " . $constraints["order"];

    return $sql;
  }

  private function executeError($sql)
  {
    $error   = ibase_errmsg();
    $message = "ibase driver execute failed: $error, SQL: $sql";
    throw new Sabel_DB_Exception($message);
  }
}

function ibase_escape_string($val)
{
  static $mqs = null;

  if ($mqs === null) {
    $mqs = (ini_get("magic_quotes_sybase") === "1");
  }

  if ($mqs) {
    return $val;
  } else {
    return str_replace("'", "''", $val);
  }
}
