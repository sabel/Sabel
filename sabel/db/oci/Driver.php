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
  
  public function connect(array $params)
  {
    $database = "//" . $params["host"] . "/" . $params["database"];
    $encoding = (isset($params["charset"])) ? $params["charset"] : null;
    
    $conn = oci_connect($params["user"], $params["password"], $database, $encoding);
    
    if ($conn) {
      $stmt = oci_parse($conn, "ALTER SESSION SET NLS_DATE_FORMAT = 'YYYY-MM-DD HH24:MI:SS'");
      oci_execute($stmt, OCI_COMMIT_ON_SUCCESS);
      return $conn;
    } else {
      $e = oci_error();
      return $e["message"];
    }
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
      throw new Sabel_DB_Driver_Exception("oci driver commit failed.");
    }
  }
  
  public function rollback()
  {
    if (oci_rollback($this->connection)) {
      $this->autoCommit = true;
    } else {
      throw new Sabel_DB_Driver_Exception("oci driver rollback failed.");
    }
  }
  
  public function close($connection)
  {
    oci_close($connection);
    unset($this->connection);
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
    $sql = $this->bind($sql, $bindParams);
    
    $execMode   = ($this->autoCommit) ? OCI_COMMIT_ON_SUCCESS : OCI_DEFAULT;
    $connection = $this->connection;
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
    throw new Sabel_DB_Driver_Exception($message . " SQL:" . $error["sqltext"]);
  }
}
