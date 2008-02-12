<?php

/**
 * Sabel_Session_Abstract
 *
 * @abstract
 * @category   Session
 * @package    org.sabel.session
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Session_Abstract extends Sabel_Object
{
  protected
    $sessionId  = "",
    $started    = false,
    $attributes = array(),
    $timeouts   = array();
  
  abstract public function start();
  abstract public function setId($id);
  abstract public function getId();
  abstract public function destroy();
  
  protected function initialize()
  {
    $this->started = true;
    
    if (empty($this->attributes)) return;
    
    $time = time();
    foreach ($this->attributes as $k => $values) {
      if (($timeout = $values["timeout"]) === 0) continue;
      if ($time >= $timeout) {
        $this->timeouts[$k] = $values;
        unset($this->attributes[$k]);
      }
    }
  }
  
  public function isStarted()
  {
    return $this->started;
  }
  
  public function has($key)
  {
    return isset($this->attributes[$key]);
  }
  
  public function read($key)
  {
    if (isset($this->attributes[$key])) {
      return $this->attributes[$key]["value"];
    } else {
      return null;
    }
  }
  
  public function write($key, $value, $timeout = 0)
  {
    if ($timeout < 0) {
      $message = "timeout value should be 0 or more.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
    
    if ($timeout !== 0) $timeout = time() + $timeout;
    $this->attributes[$key] = array("value" => $value, "timeout" => $timeout);
  }
  
  public function delete($key)
  {
    if (isset($this->attributes[$key])) {
      $value = $this->attributes[$key]["value"];
      unset($this->attributes[$key]);
      return $value;
    } else {
      return null;
    }
  }
  
  public function getTimeouts()
  {
    return $this->timeouts;
  }
}
