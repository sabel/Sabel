<?php

/**
 * Sabel_Environment
 *
 * @category   Core
 * @package    org.sabel.core
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Environment extends Sabel_Object
{
  private static $instance = null;
  private $environments = array();
  
  private function __construct() {}
  
  public static function create()
  {
    if (self::$instance === null) {
      self::$instance = new self();
    }
    
    return self::$instance;
  }
  
  public static function get($key)
  {
    return self::create()->__get($key);
  }
  
  public function set($key, $value)
  {
    $this->environments[$key] = $value;
  }
  
  public function __set($key, $value)
  {
    $this->set($key, $value);
  }
  
  public function __get($key)
  {
    if (isset($this->environments[$key])) {
      return $this->environments[$key];
    }
    
    $key = strtoupper($key);
    
    if ($key === "HTTP_HOST" && !isset($_SERVER["HTTP_HOST"])) {
      return "localhost";
    } elseif ($key === "SERVER_NAME" && !isset($_SERVER["SERVER_NAME"])) {
      return "localhost";
    } elseif ($key === "SERVER_PORT" && !isset($_SERVER["SERVER_PORT"])) {
      return "80";
    }
    
    return (isset($_SERVER[$key])) ? $_SERVER[$key] : null;
  }
  
  public function isHttps()
  {
    return (isset($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) == "on");
  }
  
  public function isWin()
  {
    $os = $this->__get("os");
    
    if ($os === null) {
      return (DIRECTORY_SEPARATOR === '\\');
    } else {
      return (substr(strtolower($os), 0, 3) === "win");
    }
  }
}
