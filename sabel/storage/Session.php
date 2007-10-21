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
class Sabel_Storage_Session extends Sabel_Object
{
  private static $instance = null;
  private static $started = false;
  
  public function __construct()
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
    if (!self::$started) {
      session_start();
      self::$started = true;
    }
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
    if (isset($_SESSION[$key])) {
      return $_SESSION[$key]["value"];
    } else {
      return null;
    }
  }
  
  public function write($key, $value, $timeout = 60)
  {
    $_SESSION[$key] = array("value"   => $value,
                            "timeout" => $timeout,
                            "count"   => 0);
  }
  
  public function delete($key)
  {
    $ret = null;
    if (isset($_SESSION[$key])) {
      $ret = $_SESSION[$key]["value"];
      unset($_SESSION[$key]);
    }
    
    return $ret;
  }
  
  public function timeout()
  {
    foreach ($_SESSION as $key => $value) {
      if ($value["count"] > $value["timeout"]) {
        unset($_SESSION[$key]);
      }
    }
  }
  
  public function countUp()
  {
    foreach ($_SESSION as $key => $value) {
      $_SESSION[$key]["count"] += 1;
    }
  }
}
