<?php

/**
 * Sabel_Env_Server
 *
 * @category   Env
 * @package    org.sabel.env
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Env_Server
{
  private static $instance = null;
  
  private function __construct()
  {
  }
  
  public static function create()
  {
    if (is_not_object(self::$instance)) self::$instance = new self();
    return self::$instance;
  }
  
  public function __get($key)
  {
    $key = strtoupper($key);
    return (isset($_SERVER[$key])) ? $_SERVER[$key] : null;
  }
  
  public function isMethod($expected)
  {
    return ($expected === $this->request_method);
  }
}
