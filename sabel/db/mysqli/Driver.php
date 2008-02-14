<?php

/**
 * Driver for MySQLi
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Mysqli_Driver extends Sabel_DB_Abstract_Driver
{
  public function getDriverId()
  {
    return "mysqli";
  }
  
  public function connect(array $params)
  {
    $h = $params["host"];
    $u = $params["user"];
    $p = $params["password"];
    $d = $params["database"];
    
    if (isset($params["port"])) {
      $conn = mysqli_connect($h, $u, $p, $d, (int)$params["port"]);
    } else {
      $conn = mysqli_connect($h, $u, $p, $d);
    }
    
    if ($conn) {
      if (isset($params["charset"])) {
        mysqli_set_charset($conn, $params["charset"]);
      }
      
      return $conn;
    } else {
      return mysqli_connect_error();
    }
  }
  
  public function begin($isolationLevel = null)
  {
    if ($isolationLevel !== null) {
      $this->setTransactionIsolationLevel($isolationLevel);
    }
    
    if (mysqli_autocommit($this->connection, false)) {
      $this->autoCommit = false;
      return $this->connection;
    } else {
      throw new Sabel_DB_Exception_Driver(mysql_error($this->connection));
    }
  }
  
  public function commit()
  {
    if (mysqli_commit($this->connection)) {
      $this->autoCommit = true;
    } else {
      throw new Sabel_DB_Exception_Driver(mysql_error($this->connection));
    }
  }
  
  public function rollback()
  {
    if (mysqli_rollback($this->connection)) {
      $this->autoCommit = true;
    } else {
      throw new Sabel_DB_Exception_Driver(mysql_error($this->connection));
    }
  }
  
  public function close($connection)
  {
    mysqli_close($connection);
    unset($this->connection);
  }
  
  public function execute($sql, $bindParams = null)
  {
    $sql = $this->bind($sql, $bindParams);
    $result = mysqli_query($this->connection, $sql);
    if (!$result) $this->executeError($sql);
    
    $rows = array();
    if (is_object($result)) {
      while ($row = mysqli_fetch_assoc($result)) $rows[] = $row;
      mysqli_free_result($result);
    }
    
    return (empty($rows)) ? null : $rows;
  }
  
  public function getLastInsertId()
  {
    return mysqli_insert_id($this->connection);
  }
  
  private function executeError($sql)
  {
    $error = mysqli_error($this->connection);
    throw new Sabel_DB_Exception_Driver("{$error}, SQL: $sql");
  }
}
