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
    $lastInsertId = null;

  public function getDriverId()
  {
    return "oci";
  }

  public function begin()
  {
    $this->autoCommit = false;
    return $this->connection;
  }

  public function commit()
  {
    if (oci_commit($this->connection)) {
      $this->autoCommit = true;
    } else {
      throw new Sabel_DB_Exception("oci driver commit failed.");
    }
  }

  public function rollback()
  {
    if (oci_rollback($this->connection)) {
      $this->autoCommit = true;
    } else {
      throw new Sabel_DB_Exception("oci driver rollback failed.");
    }
  }

  public function close($connection)
  {
    oci_close($connection);
    unset($this->connection);
  }

  public function escape(array $values)
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

  public function execute($sql, $bindParams = null)
  {
    if ($bindParams !== null) {
      $sql = $this->bind($sql, $this->escape($bindParams));
    }

    $execMode = ($this->autoCommit) ? OCI_COMMIT_ON_SUCCESS : OCI_DEFAULT;
    $ociStmt  = oci_parse($this->connection, $sql);
    $result   = oci_execute($ociStmt, $execMode);
    if (!$result) $this->executeError($ociStmt);

    if (oci_statement_type($ociStmt) === "SELECT") {
      oci_fetch_all($ociStmt, $rows, $this->offset, $this->limit, OCI_ASSOC|OCI_FETCHSTATEMENT_BY_ROW);
      $rows = array_map("array_change_key_case", $rows);
    } else {
      $rows = array();
    }

    oci_free_statement($ociStmt);

    return (empty($rows)) ? null : $rows;
  }

  public function getLastInsertId()
  {
    return $this->lastInsertId;
  }

  public function createSelectSql(Sabel_DB_Abstract_Statement $stmt)
  {
    static $nlsDateFormat = null;

    foreach ($stmt->getModel()->getSchema()->getColumns() as $column) {
      if ($column->isDatetime() && $nlsDateFormat !== "datetime") {
        $this->execute("ALTER SESSION SET NLS_DATE_FORMAT = 'YYYY-MM-DD HH24:MI:SS'");
        $nlsDateFormat = "datetime";
        break;
      } elseif ($column->isDate() && $nlsDateFormat !== "date") {
        $this->execute("ALTER SESSION SET NLS_DATE_FORMAT = 'YYYY-MM-DD'");
        $nlsDateFormat = "date";
        break;
      }
    }

    return parent::createSelectSql($stmt);
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
      $seqName = strtoupper("{$tblName}_{$column}_seq");
      $rows = $this->execute("SELECT {$seqName}.nextval AS id FROM dual");
      $this->lastInsertId = $rows[0]["id"];
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

    $this->limit  = (isset($constraints["limit"]))  ? $constraints["limit"]  : null;
    $this->offset = (isset($constraints["offset"])) ? $constraints["offset"] : null;

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
