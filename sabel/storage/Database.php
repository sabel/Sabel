<?php

/**
 * Sabel_Storage_Database
 *
 * @category   Storage
 * @package    org.sabel.storage
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Storage_Database implements Sabel_Storage
{
  /**
   * @var string
   */
  protected $connectionName = "default";
  
  /**
   * @var string
   */
  protected $tableName = "sbl_storage";
  
  /**
   * @var string
   */
  protected $namespace = "";
  
  public function __construct($config = array())
  {
    if (isset($config["namespace"])) {
      $this->setNamespace($config["namespace"]);
    }
    
    if (isset($config["tableName"])) {
      $this->tableName = $config["tableName"];
    }
    
    if (isset($config["connectionName"])) {
      $this->connectionName = $config["connectionName"];
    }
    
    if (isset($config["gcProbability"])) {
      $gcProbability = $config["gcProbability"];
    } else {
      $gcProbability = 5;
    }
    
    if (rand(1, 100) <= $gcProbability) $this->gc();
  }
  
  /**
   * @param string
   *
   * @return void
   */
  public function setConnectionName($name)
  {
    $this->connectionName = $name;
  }
  
  /**
   * @param string
   *
   * @return void
   */
  public function setTableName($tblName)
  {
    $this->tableName = $tblName;
  }
  
  /**
   * @param string
   *
   * @return void
   */
  public function setNamespace($namespace)
  {
    if (is_string($namespace)) {
      $this->namespace = $namespace;
    } else {
      $message = __METHOD__ . "() argument must be a string.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
  }
  
  public function fetch($key)
  {
    $stmt = $this->createStatement();
    $stmt->type(Sabel_DB_Statement::SELECT)
         ->projection(array("data", "timeout"))
         ->where("WHERE " . $stmt->quoteIdentifier("id") . " = @id@")
         ->setBindValue("id", $this->getKey($key));
    
    $result = $stmt->execute();
    
    if ($result === null || $result[0]["timeout"] <= time()) {
      return null;
    } else {
      return unserialize($result[0]["data"]);
    }
  }
  
  public function store($key, $value, $timeout = null)
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
    
    if ($this->has($key)) {
      $stmt->type(Sabel_DB_Statement::UPDATE)
           ->values(array("data" => serialize($value), "timeout" => $timeout))
           ->where("WHERE " . $stmt->quoteIdentifier("id") . " = @id@")
           ->setBindValue("id", $this->getKey($key));
    } else {
      $stmt->type(Sabel_DB_Statement::INSERT)
           ->values(array("id"      => $this->getKey($key),
                          "data"    => serialize($value),
                          "timeout" => $timeout));
    }
    
    $stmt->execute();
  }
  
  public function has($key)
  {
    $stmt   = $this->createStatement();
    $result = $stmt->type(Sabel_DB_Statement::SELECT)
                   ->projection("COUNT(*) AS cnt")
                   ->where("WHERE " . $stmt->quoteIdentifier("id") . " = @id@")
                   ->setBindValue("id", $this->getKey($key))
                   ->execute();
    
    return ((int)$result[0]["cnt"] !== 0);
  }
  
  public function clear($key)
  {
    $stmt = $this->createStatement();
    $stmt->type(Sabel_DB_Statement::DELETE)
         ->where("WHERE " . $stmt->quoteIdentifier("id") . " = @id@")
         ->setBindValue("id", $this->getKey($key))
         ->execute();
  }
  
  protected function gc()
  {
    $stmt = $this->createStatement();
    $stmt->type(Sabel_DB_Statement::DELETE)
         ->where("WHERE " . $stmt->quoteIdentifier("timeout") . " <= @timeout@")
         ->setBindValue("timeout", time())
         ->execute();
  }
  
  private function createStatement()
  {
    $stmt = Sabel_DB::createStatement($this->connectionName);
    $stmt->setMetadata(Sabel_DB_Metadata::getTableInfo($this->tableName, $this->connectionName));
    return $stmt;
  }
  
  private function getKey($key)
  {
    return $this->namespace . $key;
  }
}
