<?php

/**
 * Sabel_Session_Memcache
 *
 * @category   Session
 * @package    org.sabel.session
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Session_Memcache extends Sabel_Session_Ext
{
  private static $instance = null;
  
  protected $memcache = null;
  protected $newSession = false;
  
  private function __construct($server)
  {
    if (extension_loaded("memcache")) {
      $this->memcache = new Memcache();
      $port = (defined("MEMCACHED_PORT")) ? MEMCACHED_PORT : 11211;
      $this->memcache->connect($server, $port, true);
      $this->readSessionSettings();
    } else {
      throw new Sabel_Exception_Runtime("memcache extension not loaded.");
    }
  }
  
  public function getMemcache() { return $this->memcache; }
  
  public static function create($server = "localhost")
  {
    if (self::$instance === null) {
      self::$instance = new self($server);
    }
    
    return self::$instance;
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
      $newId = $this->createSessionId();
      $this->memcache->delete($this->sessionId);
      $this->memcache->set($newId, $this->attributes, 0, $this->maxLifetime);
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
      $this->memcache->delete($this->sessionId);
      return $this->attributes;
    } else {
      $message = "must start the session with start()";
      throw new Sabel_Exception_Runtime($message);
    }
  }
  
  protected function getSessionData($sessionId)
  {
    $data = $this->memcache->get($sessionId);
    
    if (is_array($data)) {
      return $data;
    } else {
      $this->newSession = true;
      return array();
    }
  }
  
  public function __destruct()
  {
    if (!$this->newSession || !empty($this->attributes)) {
      $this->memcache->set($this->sessionId, $this->attributes, 0, $this->maxLifetime);
    }
  }
}
