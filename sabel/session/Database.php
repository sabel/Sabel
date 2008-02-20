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
  protected $connectionName = "";
  
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
      $message = "the session has already been started.";
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
      
      $stmt->type(Sabel_DB_Statement::UPDATE)
           ->values(array("sid" => $newId))
           ->where("WHERE " . $stmt->quoteIdentifier("sid") . " = @currentId@")
           ->setBindValue("currentId", $this->sessionId)
           ->execute();
      
      $this->sessionId = $newId;
      $this->setSessionIdToCookie($newId);
    } else {
      $message = "must start the session with start()";
      throw new Sabel_Exception_Runtime($message);
    }
  }
  
  public function destroy()
  {
    if ($this->started) {
      $stmt = $this->createStatement();
      $stmt->type(Sabel_DB_Statement::DELETE)
           ->where("WHERE " . $stmt->quoteIdentifier("sid") . " = @sid@")
           ->setBindValue("sid", $this->sessionId)
           ->execute();
      
      $attributes = $this->attributes;
      $this->attributes = array();
      return $attributes;
    } else {
      $message = "must start the session with start()";
      throw new Sabel_Exception_Runtime($message);
    }
  }
  
  protected function getSessionData($sessionId)
  {
    $stmt = $this->createStatement();
    $stmt->type(Sabel_DB_Statement::SELECT)
         ->projection(array("sdata", "timeout"))
         ->where("WHERE " . $stmt->quoteIdentifier("sid") . " = @sid@")
         ->setBindValue("sid", $sessionId);
    
    if (($result = $stmt->execute()) === null) {
      $this->newSession = true;
      return array();
    } elseif ($result[0]["timeout"] <= time()) {
      return array();
    } else {
      return unserialize($result[0]["sdata"]);
    }
  }
  
  protected function sessionIdExists($sessionId)
  {
    $stmt   = $this->createStatement();
    $result = $stmt->type(Sabel_DB_Statement::SELECT)
                   ->projection("COUNT(*) AS cnt")
                   ->where("WHERE " . $stmt->quoteIdentifier("sid") . " = @sid@")
                   ->setBindValue("sid", $sessionId)
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
      $stmt->type(Sabel_DB_Statement::DELETE)
           ->where("WHERE " . $stmt->quoteIdentifier("timeout") . " <= @timeout@")
           ->setBindValue("timeout", time())
           ->execute();
    }
  }
  
  private function createStatement()
  {
    $stmt = Sabel_DB::createStatement($this->connectionName);
    $stmt->setMetadata(Sabel_DB_Metadata::getTableInfo($this->tableName, $this->connectionName));
    return $stmt;
  }
  
  public function __destruct()
  {
    static $ran = false;
    
    if ($ran || $this->newSession && empty($this->attributes)) return;
    
    $stmt = $this->createStatement();
    $timeoutValue = time() + $this->maxLifetime;
    
    if ($this->sessionIdExists($this->sessionId)) {
      $stmt->type(Sabel_DB_Statement::UPDATE)
           ->values(array("sdata" => serialize($this->attributes), "timeout" => $timeoutValue))
           ->where("WHERE " . $stmt->quoteIdentifier("sid") . " = @sid@")
           ->setBindValue("sid", $this->sessionId);
    } else {
      $stmt->type(Sabel_DB_Statement::INSERT)
           ->values(array("sid"     => $this->sessionId,
                          "sdata"   => serialize($this->attributes),
                          "timeout" => $timeoutValue));
    }
    
    $stmt->execute();
    $ran = true;
  }
}
