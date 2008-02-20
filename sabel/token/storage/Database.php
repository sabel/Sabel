<?php

/**
 * Sabel_Token_Storage_Database
 *
 * @category   Token
 * @package    org.sabel.token
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Token_Storage_Database implements Sabel_Token_Storage
{
  /**
   * @var self
   */
  private static $instance = null;
  
  /**
   * @var string
   */
  protected $connectionName = "sbl_token_storage";
  
  /**
   * @var string
   */
  protected $tableName = "sbl_token";
  
  /**
   * @var string
   */
  protected $namespace = "";
  
  public function __construct($namespace = "")
  {
    $this->namespace = $namespace;
    $this->gc();
  }
  
  public function setConnectionName($name)
  {
    $this->connectionName = $name;
  }
  
  public function setTableName($tblName)
  {
    $this->tableName = $tblName;
  }
  
  public function fetch($token)
  {
    $stmt = $this->createStatement();
    $stmt->type(Sabel_DB_Statement::SELECT)
         ->projection(array("data", "timeout"))
         ->where("WHERE " . $stmt->quoteIdentifier("id") . " = @id@")
         ->setBindValue("id", $this->getKey($token));
    
    $result = $stmt->execute();
    
    if ($result === null || $result[0]["timeout"] <= time()) {
      return null;
    } else {
      return unserialize($result[0]["data"]);
    }
  }
  
  public function store($token, $value, $timeout = null)
  {
    if ($timeout === null) {
      $timeout = time() + ini_get("session.gc_maxlifetime");
    } elseif (!is_numeric($timeout) || $timeout < 1) {
      $message = "invalid timeout value.";
      throw new Sabel_Exception_InvalidArgument($message);
    } else {
      $timeout += time();
    }
    
    $stmt = $this->createStatement();
    
    if ($this->has($token)) {
      $stmt->type(Sabel_DB_Statement::UPDATE)
           ->values(array("data" => serialize($value), "timeout" => $timeout))
           ->where("WHERE " . $stmt->quoteIdentifier("id") . " = @id@")
           ->setBindValue("id", $this->getKey($token));
    } else {
      $stmt->type(Sabel_DB_Statement::INSERT)
           ->values(array("id"      => $this->getKey($token),
                          "data"    => serialize($value),
                          "timeout" => $timeout));
    }
    
    $stmt->execute();
  }
  
  public function has($token)
  {
    $stmt   = $this->createStatement();
    $result = $stmt->type(Sabel_DB_Statement::SELECT)
                   ->projection("COUNT(*) AS cnt")
                   ->where("WHERE " . $stmt->quoteIdentifier("id") . " = @id@")
                   ->setBindValue("id", $this->getKey($token))
                   ->execute();
    
    return ((int)$result[0]["cnt"] !== 0);
  }
  
  public function clear($token)
  {
    $stmt = $this->createStatement();
    $stmt->type(Sabel_DB_Statement::DELETE)
         ->where("WHERE " . $stmt->quoteIdentifier("id") . " = @id@")
         ->setBindValue("id", $this->getKey($token))
         ->execute();
  }
  
  protected function gc()
  {
    // @todo
  }
  
  private function createStatement()
  {
    $stmt = Sabel_DB::createStatement($this->connectionName);
    $stmt->setMetadata(Sabel_DB_Metadata::getTableInfo($this->tableName, $this->connectionName));
    return $stmt;
  }
  
  private function getKey($token)
  {
    if ($this->namespace === "") {
      return $token;
    } else {
      return $this->namespace . "_" . $token;
    }
  }
}
