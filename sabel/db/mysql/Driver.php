<?php

/**
 * Driver for MySQL
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Mysql_Driver extends Sabel_DB_Abstract_Driver
{
  public function getDriverId()
  {
    return "mysql";
  }
  
  public function connect(array $params)
  {
    $host = $params["host"];
    $host = (isset($params["port"])) ? $host . ":" . $params["port"] : $host;
    $conn = mysql_connect($host, $params["user"], $params["password"], true);
    
    if ($conn) {
      if (!mysql_select_db($params["database"], $conn)) {
        return mysql_error();
      }
      
      if (isset($params["charset"])) {
        list (, , $v) = explode(".", PHP_VERSION);
        if ($v{0} >= 3) {
          mysql_set_charset($params["charset"], $conn);
        } else {
          mysql_query("SET NAMES " . $params["charset"], $conn);
        }
      }
      
      return $conn;
    } else {
      return mysql_error();
    }
  }
  
  public function begin($isolationLevel = null)
  {
    if ($isolationLevel !== null) {
      $this->setTransactionIsolationLevel($isolationLevel);
    }
    
    $this->execute("START TRANSACTION");
    $this->autoCommit = false;
    return $this->connection;
  }
  
  public function commit()
  {
    $this->execute("COMMIT");
    $this->autoCommit = true;
  }
  
  public function rollback()
  {
    $this->execute("ROLLBACK");
    $this->autoCommit = true;
  }
  
  public function close($connection)
  {
    mysql_close($connection);
    unset($this->connection);
  }
  
  public function execute($sql, $bindParams = null)
  {
    $sql = $this->bind($sql, $bindParams);
    $result = mysql_query($sql, $this->connection);
    if (!$result) $this->executeError($sql);
    
    $rows = array();
    if (is_resource($result)) {
      while ($row = mysql_fetch_assoc($result)) $rows[] = $row;
      mysql_free_result($result);
    }
    
    return (empty($rows)) ? null : $rows;
  }
  
  public function getLastInsertId()
  {
    return mysql_insert_id($this->connection);
  }
  
  private function executeError($sql)
  {
    $error = mysql_error($this->connection);
    throw new Sabel_DB_Exception_Driver("{$error}, SQL: $sql");
  }
}
