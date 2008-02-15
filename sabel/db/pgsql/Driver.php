<?php

/**
 * Driver for PostgreSQL
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Pgsql_Driver extends Sabel_DB_Abstract_Driver
{
  public function getDriverId()
  {
    return "pgsql";
  }
  
  public function connect(array $params)
  {
    $host = $params["host"];
    $user = $params["user"];
    $pass = $params["password"];
    $dbs  = $params["database"];
    
    $host = (isset($params["port"])) ? $host . " port=" . $params["port"] : $host;
    $conn = pg_connect("host={$host} dbname={$dbs} user={$user} password={$pass}",
                       PGSQL_CONNECT_FORCE_NEW);
    
    if ($conn) {
      if (isset($params["charset"])) {
        pg_set_client_encoding($conn, $params["charset"]);
      }
      
      return $conn;
    } else {
      return "cannot connect to PostgreSQL. please check your configuration.";
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
    pg_close($connection);
    unset($this->connection);
  }
  
  public function execute($sql, $bindParams = null)
  {
    $sql = $this->bind($sql, $bindParams);
    $result = pg_query($this->connection, $sql);
    if (!$result) $this->executeError($sql);
    
    $rows = array();
    if (is_resource($result)) {
      $rows = pg_fetch_all($result);
      pg_free_result($result);
    }
    
    return (empty($rows)) ? null : $rows;
  }
  
  public function getLastInsertId()
  {
    $rows = $this->execute("SELECT LASTVAL() AS id");
    return $rows[0]["id"];
  }
  
  private function executeError($sql)
  {
    $error = pg_last_error($this->connection);
    throw new Sabel_DB_Exception_Driver("{$error}, SQL: $sql");
  }
}
