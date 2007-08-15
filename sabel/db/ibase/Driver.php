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
class Sabel_DB_Ibase_Driver extends Sabel_DB_Abstract_Common_Driver
{
  protected $driverId      = "ibase";
  protected $execFunction  = "ibase_query";
  protected $closeFunction = "ibase_close";
  protected $lastInsertId  = null;

  public function loadTransaction()
  {
    return Sabel_DB_Ibase_Transaction::getInstance();
  }

  public function getConnection()
  {
    $connection = $this->loadTransaction()->get($this->getConnectionName());

    if ($connection === null) {
      $connection = parent::getConnection();
      $this->autoCommit = true;
    } else {
      $this->autoCommit = false;
    }

    return $this->connection = $connection;
  }

  public function begin($connectionName = null)
  {
    if ($connectionName === null) {
      $connectionName = $this->connectionName;
    }

    $trans = $this->loadTransaction();

    if (!$trans->isActive($connectionName)) {
      $connection = Sabel_DB_Connection::get($connectionName);
      $resource = ibase_trans(IBASE_COMMITTED|IBASE_REC_NO_VERSION, $connection);
      $trans->start($resource, $connectionName);
    }
  }

  public function escape($values)
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

  public function execute(Sabel_DB_Abstract_Statement $stmt)
  {
    if (($bindParams = $stmt->getBindParams()) !== null) {
      $bindParams = $this->escape($bindParams);
    }

    $conn   = $this->getConnection();
    $sql    = $this->bind($stmt->getSql(), $bindParams);
    $result = ibase_query($conn, $sql);

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
    $binds   = array();
    $tblName = $stmt->getTable();
    $keys    = array_keys($stmt->getValues());

    if (($column = $stmt->getSequenceColumn()) !== null) {
      $keys[] = $column;
      $genName = strtoupper("{$tblName}_{$column}_gen");
      $this->lastInsertId = ibase_gen_id($genName, 1, $this->getConnection());
      $stmt->setBind(array($column => $this->lastInsertId));
    }

    foreach ($keys as $key) $binds[] = ":" . $key;

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

if (!defined("MQ_SYBASE")) {
  define("MQ_SYBASE", ini_get("magic_quotes_sybase"));
}

function ibase_escape_string($val)
{
  if (MQ_SYBASE) {
    return $val;
  } else {
    return str_replace("'", "''", $val);
  }
}
