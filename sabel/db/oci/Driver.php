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

  public function getSqlBuilder($stmt)
  {
    $sqlBuilder = new Sabel_DB_Oci_Sql($stmt);
    $sqlBuilder->setDriver($this);

    return $sqlBuilder;
  }

  public function begin()
  {
    $this->autoCommit = false;
    return $this->getConnection();
  }

  public function commit()
  {
    if (oci_commit($this->getConnection())) {
      $this->autoCommit = true;
    } else {
      throw new Sabel_DB_Exception("oci driver commit failed.");
    }
  }

  public function rollback()
  {
    if (oci_rollback($this->getConnection())) {
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

  public function setLimit($limit)
  {
    $this->limit = $limit;
  }

  public function setOffset($offset)
  {
    $this->offset = $offset;
  }

  public function setLastInsertId($id)
  {
    $this->lastInsertId = $id;
  }

  public function execute($sql, $bindParams = null)
  {
    if ($bindParams !== null) {
      $sql = $this->bind($sql, $this->escape($bindParams));
    }

    $execMode   = ($this->autoCommit) ? OCI_COMMIT_ON_SUCCESS : OCI_DEFAULT;
    $connection = $this->getConnection();
    $ociStmt    = oci_parse($connection, $sql);
    $result     = oci_execute($ociStmt, $execMode);

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
