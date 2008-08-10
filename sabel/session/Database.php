<?php

/**
 * Sabel_Session_Database
 *
 * @category   Session
 * @package    org.sabel.session
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Session_Database extends Sabel_Session_Ext
{
  /**
   * @var self
   */
  private static $instance = null;
  
  /**
   * @var string
   */
  protected $connectionName = "default";
  
  /**
   * @var string
   */
  protected $tableName = "sbl_session";
  
  /**
   * @var boolean
   */
  protected $newSession = false;
  
  private function __construct($connectionName)
  {
    $this->readSessionSettings();
    $this->connectionName = $connectionName;
  }
  
  public static function create($connectionName = "default")
  {
    if (self::$instance === null) {
      self::$instance = new self($connectionName);
      register_shutdown_function(array(self::$instance, "destruct"));
    }
    
    return self::$instance;
  }
  
  public function setConnectionName($name)
  {
    $this->connectionName = $name;
  }
  
  public function setTableName($tblName)
  {
    $this->tableName = $tblName;
  }
  
  public function start()
  {
    if ($this->started) return;
    if (!$sessionId = $this->initSession()) return;
    
    if ($this->sessionId === "") {
      $this->sessionId  = $sessionId;
      $this->attributes = $this->getSessionData($this->sessionId);
    } else {
      $this->attributes = $this->getSessionData($sessionId);
    }
    
    $this->initialize();
    $this->gc();
  }
  
  public function setId($id)
  {
    if ($this->started) {
      $message = __METHOD__ . "() the session has already been started.";
      throw new Sabel_Exception_Runtime($message);
    } else {
      $this->sessionId = $id;
    }
  }
  
  public function regenerateId()
  {
    if ($this->started) {
      $newId = $this->createSessionId();
      $stmt  = $this->createStatement();
      
      $stmt->type(Sabel_Db_Statement::UPDATE)
           ->values(array("id" => $newId))
           ->where("WHERE " . $stmt->quoteIdentifier("id") . " = @currentId@")
           ->setBindValue("currentId", $this->sessionId)
           ->execute();
      
      $this->sessionId = $newId;
      $this->setSessionIdToCookie($newId);
    } else {
      $message = __METHOD__ . "() must start the session with start()";
      throw new Sabel_Exception_Runtime($message);
    }
  }
  
  public function destroy()
  {
    if ($this->started) {
      $stmt = $this->createStatement();
      $stmt->type(Sabel_Db_Statement::DELETE)
           ->where("WHERE " . $stmt->quoteIdentifier("id") . " = @id@")
           ->setBindValue("id", $this->sessionId)
           ->execute();
      
      $attributes = $this->attributes;
      $this->attributes = array();
      return $attributes;
    } else {
      $message = __METHOD__ . "() must start the session with start()";
      throw new Sabel_Exception_Runtime($message);
    }
  }
  
  protected function getSessionData($sessionId)
  {
    $stmt = $this->createStatement();
    $stmt->type(Sabel_Db_Statement::SELECT)
         ->projection(array("data", "timeout"))
         ->where("WHERE " . $stmt->quoteIdentifier("id") . " = @id@")
         ->setBindValue("id", $sessionId);
    
    if (($result = $stmt->execute()) === null) {
      $this->newSession = true;
      return array();
    } elseif ($result[0]["timeout"] <= time()) {
      return array();
    } else {
      return unserialize(str_replace("\\000", "\000", $result[0]["data"]));
    }
  }
  
  protected function sessionIdExists($sessionId)
  {
    $stmt   = $this->createStatement();
    $result = $stmt->type(Sabel_Db_Statement::SELECT)
                   ->projection("COUNT(*) AS cnt")
                   ->where("WHERE " . $stmt->quoteIdentifier("id") . " = @id@")
                   ->setBindValue("id", $sessionId)
                   ->execute();
    
    return ((int)$result[0]["cnt"] !== 0);
  }
  
  protected function gc()
  {
    $probability = ini_get("session.gc_probability");
    $divisor     = ini_get("session.gc_divisor");
    
    if ($probability === "") $probability = 1;
    if ($divisor     === "") $divisor     = 100;
    
    if (rand(1, $divisor) <= $probability) {
      $stmt = $this->createStatement();
      $stmt->type(Sabel_Db_Statement::DELETE)
           ->where("WHERE " . $stmt->quoteIdentifier("timeout") . " <= @timeout@")
           ->setBindValue("timeout", time())
           ->execute();
    }
  }
  
  private function createStatement()
  {
    $stmt = Sabel_Db::createStatement($this->connectionName);
    $stmt->setMetadata(Sabel_Db_Metadata::getTableInfo($this->tableName, $this->connectionName));
    return $stmt;
  }
  
  public function destruct()
  {
    if ($this->newSession && empty($this->attributes)) return;
    
    $stmt    = Sabel_Db::createStatement($this->connectionName);
    $value   = str_replace("\000", "\\000", serialize($this->attributes));
    $timeout = time() + $this->maxLifetime;
    $table   = $stmt->quoteIdentifier($this->tableName);
    $idCol   = $stmt->quoteIdentifier("id");
    $dataCol = $stmt->quoteIdentifier("data");
    $toutCol = $stmt->quoteIdentifier("timeout");
    
    if ($this->sessionIdExists($this->sessionId)) {
      $query = "UPDATE $table SET $dataCol = @data@, $toutCol = $timeout "
             . "WHERE $idCol = '{$this->sessionId}'";
    } else {
      $query = "INSERT INTO $table ({$idCol}, {$dataCol}, {$toutCol}) "
             . "VALUES ('{$this->sessionId}', @data@, {$timeout})";
    }
    
    $stmt->setQuery($query)
         ->setBindValue("data", $value)
         ->execute();
  }
}
