<?php

/**
 * Sabel_Environment
 *
 * @category   Env
 * @package    org.sabel.environment
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Environment extends Sabel_Object
{
  private static $instance = null;
  private $environments = array();
  
  public static function create()
  {
    if (self::$instance === null) {
      self::$instance = new self();
    }
    
    return self::$instance;
  }
  
  public static function get($key)
  {
    return self::create()->$key;
  }
  
  public function __get($key)
  {
    if (isset($this->environments[$key])) {
      return $this->environments[$key];
    }
    
    $key = strtoupper($key);
    
    if ($key === "HTTP_HOST" && !isset($_SERVER["HTTP_HOST"])) {
      return "localhost";
    } elseif ($key === "SERVER_PORT" && !isset($_SERVER["SERVER_PORT"])) {
      return "80";
    }
    
    return (isset($_SERVER[$key])) ? $_SERVER[$key] : null;
  }
  
  public function set($key, $value)
  {
    $this->environments[$key] = $value;
  }
  
  public function isHttps()
  {
    return (isset($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) == "on");
  }
  
  public function isMethod($expected)
  {
    return ($expected === $this->request_method);
  }
}
