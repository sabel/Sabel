<?php

/**
 * Sabel_DB_Oci_Driver
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Oci_Driver extends Sabel_DB_Abstract_Driver
{
  private
    $limit        = null,
    $offset       = null,
    $lastInsertId = null,
    $execMode     = OCI_COMMIT_ON_SUCCESS;

  public function getDriverId()
  {
    return "oci";
  }

  public function loadTransaction()
  {
    return Sabel_DB_Transaction_General::getInstance();
  }

  public function getConnection()
  {
    $connection = $this->loadTransaction()->getConnection($this->connectionName);

    if ($connection === null) {
      $this->execMode = OCI_COMMIT_ON_SUCCESS;
      return parent::getConnection();
    } else {
      $this->execMode = OCI_DEFAULT;
      return $connection;
    }
  }

  public function begin($connectionName = null)
  {
    if ($connectionName === null) {
      $connectionName = $this->connectionName;
    }

    $trans = $this->loadTransaction();

    if (!$trans->isActive($connectionName)) {
      $connection = Sabel_DB_Connection::get($connectionName);
      $trans->start($connection, $this);
    }
  }

  public function commit($connection)
  {
    oci_commit($connection);
  }

  public function rollback($connection)
  {
    oci_rollback($connection);
  }

  public function close($connection)
  {
    oci_close($connection);
    unset($this->connection);
  }

  public function escape($values)
  {
    foreach ($values as &$val) {
      if (is_bool($val)) {
        $val = ($val) ? 1 : 0;
      } elseif (is_string($val)) {
        $val = "'" . oci_escape_string($val) . "'";
      }
    }

    return $values;
  }

  public function execute(Sabel_DB_Abstract_Statement $stmt)
  {
    if (($bindParams = $stmt->getBindParams()) !== null) {
      $bindParams = $this->escape($bindParams);
    }

    $conn    = $this->getConnection();
    $sql     = $this->bind($stmt->getSql(), $bindParams);
    $ociStmt = oci_parse($conn, $sql);
    $result  = oci_execute($ociStmt, $this->execMode);

    if (!$result) $this->executeError($ociStmt);

    if (oci_statement_type($ociStmt) === "SELECT") {
      oci_fetch_all($ociStmt, $rows, $this->offset, $this->limit, OCI_ASSOC|OCI_FETCHSTATEMENT_BY_ROW);
      $rows = array_map("array_change_key_case", $rows);
    } else {
      $rows = array();
    }

    oci_free_statement($ociStmt);

    // @todo...
    // $this->limit = $this->offset = null;

    return (empty($rows)) ? null : $rows;
  }

  public function getLastInsertId()
  {
    return $this->lastInsertId;
  }

  public function createInsertSql(Sabel_DB_Abstract_Statement $stmt)
  {
    $binds   = array();
    $tblName = $stmt->getTable();
    $keys    = array_keys($stmt->getValues());

    if (($column = $stmt->getSequenceColumn()) !== null) {
      $keys[] = $column;
      $seqName = strtoupper("{$tblName}_{$column}_seq");
      $seqStmt = Sabel_DB_Statement::create($this, Sabel_DB_Statement::SELECT);
      $rows = $seqStmt->setSql("SELECT {$seqName}.nextval AS id FROM dual")->execute();
      $this->lastInsertId = $rows[0]["id"];
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

    if (isset($constraints["limit"])) {
      $this->limit = $constraints["limit"];
    } else {
      $this->limit = null;
    }

    if (isset($constraints["offset"])) {
      $this->offset = $constraints["offset"];
    } else {
      $this->offset = null;
    }

    return $sql;
  }

  private function executeError($ociStmt)
  {
    $error   = oci_error($ociStmt);
    $message = "oci driver execute failed: " . $error["message"];
    throw new Sabel_DB_Exception($message);
  }
}

function oci_escape_string($val)
{
  return str_replace("'", "''", $val);
}
