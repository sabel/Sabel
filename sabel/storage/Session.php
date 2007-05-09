<?php

/**
 * Storage of session
 *
 * @category   Storage
 * @package    org.sabel.storage
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Storage_Session
{
  private static $instance = null;
  private static $started = false;
  
  public static function create()
  {
    if (!self::$instance) self::$instance = new self();
    return self::$instance;
  }
  
  public function __construct()
  {
    $this->start();
  }
  
  public function start()
  {
    if (self::$started === false) {
      session_start();
      self::$started = true;
    }
  }
  
  public function clear()
  {
    $deleted = array();
    foreach ($_SESSION as $key => $sesval) {
      $deleted[] = $sesval;
      unset($_SESSION[$key]);
    }
    return $deleted;
  }
  
  public function destroy()
  {
    $deleted = $_SESSION;
    session_destroy();
    return $deleted;
  }
  
  public function has($key)
  {
    return isset($_SESSION[$key]);
  }
  
  public function read($key)
  {
    $ret = null;
    if (isset($_SESSION[$key])) {
      $ret = $_SESSION[$key]["value"];
    }
    return $ret;
  }
  
  public function write($key, $value, $timeout = 60)
  {
    if (self::$started === false) $this->start();
    
    $_SESSION[$key] = array('value'   => $value, 
                            'timeout' => $timeout,
                            'count'   => 0);
  }
  
  public function delete($key)
  {
    $ret = null;
    if (isset($_SESSION[$key])) {
      $ret =& $_SESSION[$key]['value'];
      unset($_SESSION[$key]);
    }
    return $ret;
  }
  
  public function timeout()
  {
    foreach ($_SESSION as $key => $value) {
      if ($value['count'] > $value['timeout']) {
        unset($_SESSION[$key]);
      }
    }
  }
  
  public function countUp()
  {
    foreach ($_SESSION as $key => $value) {
      $_SESSION[$key]['count'] += 1;
    }
  }
}
