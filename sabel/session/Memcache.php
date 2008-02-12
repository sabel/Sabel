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
class Sabel_Session_Memcache extends Sabel_Session_Abstract
{
  private static $instance = null;
  
  private $memcache = null;
  
  private function __construct()
  {
    if (extension_loaded("memcache")) {
      $this->memcache = new Memcache();
      $port = (defined("MEMCACHED_PORT")) ? MEMCACHED_PORT : 11211;
      $this->memcache->connect($server, $port, true);
    } else {
      throw new Sabel_Exception_Runtime("memcache extension not loaded.");
    }
  }
  
  public static function create($server = "localhost")
  {
    if (self::$instance === null) {
      self::$instance = new self($server);
    }
    
    return self::$instance;
  }
  
  public function start()
  {
    /*
    if (!$this->started) {
      session_start();
      $this->sessionId  = session_id();
      $this->attributes =& $_SESSION;
      $this->initialize();
    }
    */
  }
  
  public function setId($id)
  {
    /*
    if ($this->started) {
      $message = "the session has already been started.";
      throw new Sabel_Exception_Runtime($message);
    } else {
      session_id($id);
      $this->sessionId = $id;
    }
    */
  }
  
  public function getId()
  {
    /*
    return $this->sessionId;
    */
  }
  
  public function regenerateId()
  {
    /*
    if ($this->started) {
      session_regenerate_id(true);
      $this->sessionId = session_id();
    } else {
      $message = "must start the session with start()";
      throw new Sabel_Exception_Runtime($message);
    }
    */
  }
  
  public function destroy()
  {
    /*
    if ($this->started) {
      // @todo process for the cookie.
      
      $attributes = $this->attributes;
      session_destroy();
      return $attributes;
    } else {
      $message = "must start the session with start()";
      throw new Sabel_Exception_Runtime($message);
    }
    */
  }
}
