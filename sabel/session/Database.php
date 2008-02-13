<?php

/**
 * Sabel_Session_Database
 *
 * @category   Session
 * @package    org.sabel.session
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Session_Database extends Sabel_Session_Ext
{
  private static $instance = null;
  
  protected $connectionName = "";
  protected $tableName = "session";
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
    
    if (($sessionId = $this->getSessionId()) === "") {
      $sessionId = $this->createSessionId();
    }
    
    if ($this->sessionId === "") {
      $this->sessionId  = $sessionId;
      $this->attributes = $this->getSessionData($this->sessionId);
    } else {
      $this->attributes = $this->getSessionData($sessionId);
    }
    
    $this->setSessionIdToCookie();
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
  
  public function getId()
  {
    return $this->sessionId;
  }
  
  public function regenerateId()
  {
    if ($this->started) {
      $newId   = $this->createSessionId();
      $stmt    = Sabel_DB::createStatement($this->connectionName);
      $tblName = $stmt->quoteIdentifier($this->tableName);
      $sid     = $stmt->quoteIdentifier("sid");
      $escaped = $stmt->escape(array($this->sessionId, $newId));
      $query   = "UPDATE $tblName SET $sid = {$escaped[1]} WHERE $sid = {$escaped[0]}";
      $stmt->setQuery($query)->execute();
      
      $this->sessionId = $newId;
      $this->setSessionIdToCookie();
    } else {
      $message = "must start the session with start()";
      throw new Sabel_Exception_Runtime($message);
    }
  }
  
  public function destroy()
  {
    if ($this->started) {
      $stmt    = Sabel_DB::createStatement($this->connectionName);
      $tblName = $stmt->quoteIdentifier($this->tableName);
      $sid     = $stmt->quoteIdentifier("sid");
      $escaped = $stmt->escape(array($this->sessionId));
      $query   = "DELETE FROM $tblName WHERE $sid = {$escaped[0]}";
      $stmt->setQuery($query)->execute();
      return $this->attributes;
    } else {
      $message = "must start the session with start()";
      throw new Sabel_Exception_Runtime($message);
    }
  }
  
  protected function getSessionData($sessionId)
  {
    $stmt    = Sabel_DB::createStatement($this->connectionName);
    $tblName = $stmt->quoteIdentifier($this->tableName);
    $sid     = $stmt->quoteIdentifier("sid");
    $sdata   = $stmt->quoteIdentifier("sdata");
    $timeout = $stmt->quoteIdentifier("timeout");
    $escaped = $stmt->escape(array($sessionId));
    
    $query = "SELECT {$sdata}, {$timeout} "
           . "FROM $tblName WHERE $sid = {$escaped[0]}";
    
    $result = $stmt->setQuery($query)->execute();
    
    if ($result === null) {
      $this->newSession = true;
      return array();
    } else {
      return unserialize($result[0]["sdata"]);
    }
  }
  
  protected function sessionIdExists($id)
  {
    $stmt    = Sabel_DB::createStatement($this->connectionName);
    $tblName = $stmt->quoteIdentifier($this->tableName);
    $sid     = $stmt->quoteIdentifier("sid");
    $escaped = $stmt->escape(array($id));
    $query   = "SELECT $sid FROM $tblName WHERE $sid = {$escaped[0]}";
    
    return ($stmt->setQuery($query)->execute() !== null);
  }
  
  protected function gc()
  {
    $probability = ini_get("session.gc_probability");
    $divisor     = ini_get("session.gc_divisor");
    if ($probability === "") $probability = 1;
    if ($divisor     === "") $divisor     = 100;
    
    if (rand(0, $divisor) <= $probability) {
      $stmt    = Sabel_DB::createStatement($this->connectionName);
      $tblName = $stmt->quoteIdentifier($this->tableName);
      $sid     = $stmt->quoteIdentifier("sid");
      $timeout = $stmt->quoteIdentifier("timeout");
      $query   = "DELETE FROM $tblName WHERE $timeout <= " . time();
      $stmt->setQuery($query)->execute();
    }
  }
  
  public function __destruct()
  {
    if ($this->newSession && empty($this->attributes)) return;
    
    $stmt    = Sabel_DB::createStatement($this->connectionName);
    $tblName = $stmt->quoteIdentifier($this->tableName);
    $sid     = $stmt->quoteIdentifier("sid");
    $sdata   = $stmt->quoteIdentifier("sdata");
    $timeout = $stmt->quoteIdentifier("timeout");
    $escaped = $stmt->escape(array($this->sessionId, serialize($this->attributes)));
    
    $timeoutValue = time() + $this->maxLifetime;
    
    if ($this->sessionIdExists($this->sessionId)) {
      $query = "UPDATE $tblName SET $sdata = {$escaped[1]}, "
             . "$timeout = $timeoutValue WHERE $sid = {$escaped[0]}";
    } else {
      $query = "INSERT INTO {$tblName}({$sid}, {$sdata}, {$timeout}) VALUES("
             . "{$escaped[0]}, {$escaped[1]}, {$timeoutValue})";
    }
    
    $stmt->setQuery($query)->execute();
  }
}
