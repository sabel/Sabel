<?php

class Sabel_Storage_Session
{
  private static $instance = null;
  
  public function __construct()
  {
    // @todo fix me
    if (!defined('TEST_CASE')) @session_start();
  }
  
  public static function create()
  {
    if (!self::$instance) self::$instance = new self();
    return self::$instance;
  }
  
  public function clear()
  {
    $deleted = array();
    foreach ($_SESSION as $key => $sesval) {
      if ($key === SecurityUser::AUTHORIZE_NAMESPACE) {
        SecurityUser::create()->unAuthorize();
      } else {
        $deleted[] = $sesval;
        unset($_SESSION[$key]);
      }
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
      $ret = $_SESSION[$key]['value'];
    }
    return $ret;
  }
  
  public function write($key, $value, $timeout = 60)
  {
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
