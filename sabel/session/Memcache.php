<?php

/**
 * Sabel_Session_Memcache
 *
 * @category   Session
 * @package    org.sabel.session
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Session_Memcache extends Sabel_Session_Ext
{
  /**
   * @var self
   */
  private static $instance = null;
  
  /**
   * @var Memcache
   */
  protected $memcache = null;
  
  /**
   * @var boolean
   */
  protected $newSession = false;
  
  private function __construct($server, $port)
  {
    if (extension_loaded("memcache")) {
      $this->memcache = new Memcache();
      $this->addServer($server, $port);
      $this->readSessionSettings();
    } else {
      throw new Sabel_Exception_Runtime("memcache extension not loaded.");
    }
  }
  
  public static function create($server = "localhost", $port = 11211)
  {
    if (self::$instance === null) {
      self::$instance = new self($server, $port);
      register_shutdown_function(array(self::$instance, "destruct"));
    }
    
    return self::$instance;
  }
  
  public function addServer($server, $port = 11211, $weight = 1)
  {
    $this->memcache->addServer($server, $port, true, $weight);
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
      $this->memcache->delete($this->sessionId);
      $this->memcache->set($newId, $this->attributes, 0, $this->maxLifetime);
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
      $this->memcache->delete($this->sessionId);
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
    $data = $this->memcache->get($sessionId);
    
    if (is_array($data)) {
      return $data;
    } else {
      $this->newSession = true;
      return array();
    }
  }
  
  public function destruct()
  {
    if (!$this->newSession || !empty($this->attributes)) {
      $this->memcache->set($this->sessionId, $this->attributes, 0, $this->maxLifetime);
    }
  }
}
