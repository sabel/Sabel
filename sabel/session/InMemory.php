<?php

/**
 * Sabel_Session_InMemory
 *
 * @category   Session
 * @package    org.sabel.session
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2002-2006 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Session_InMemory extends Sabel_Session_Abstract
{
  private static $instance = null;
  
  private function __construct()
  {
    
  }
  
  public static function create()
  {
    if (self::$instance === null) {
      self::$instance = new self();
    }
    
    return self::$instance;
  }
  
  public function start()
  {
    if (!$this->started) {
      $this->sessionId = $this->createSessionId();
      $this->initialize();
    }
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
      $this->sessionId = $this->createSessionId();
    } else {
      $message = "must start the session with start()";
      throw new Sabel_Exception_Runtime($message);
    }
  }
  
  public function destroy()
  {
    if ($this->started) {
      $attributes = $this->attributes;
      $this->attributes = array();
      return $attributes;
    } else {
      $message = "must start the session with start()";
      throw new Sabel_Exception_Runtime($message);
    }
  }
}
