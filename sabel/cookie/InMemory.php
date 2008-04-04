<?php

/**
 * Sabel_Cookie_InMemory
 *
 * @category   Cookie
 * @package    org.sabel.cookie
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Cookie_InMemory extends Sabel_Cookie_Abstract
{
  private static $instance = null;
  
  protected function $cookies = array();
  
  private function __construct() {}
  
  public static function create()
  {
    if (self::$instance === null) {
      self::$instance = new self();
    }
    
    return self::$instance;
  }
  
  public function set($key, $value, $options = array())
  {
    $options = $this->createOptions($options);
    
    $this->cookies[$key] = array("value"    => urlencode($value),
                                 "expire"   => $options["expire"],
                                 "path"     => $options["path"],
                                 "domain"   => $options["domain"],
                                 "secure"   => $options["secure"],
                                 "httpOnly" => $options["httpOnly"]);
  }
  
  public function rawset($key, $value, $options = array())
  {
    $options = $this->createOptions($options);
    
    $this->cookies[$key] = array("value"    => $value,
                                 "expire"   => $options["expire"],
                                 "path"     => $options["path"],
                                 "domain"   => $options["domain"],
                                 "secure"   => $options["secure"],
                                 "httpOnly" => $options["httpOnly"]);
  }
  
  public function get($key)
  {
    if (array_key_exists($key, $this->cookies)) {
      // @todo check
      return $this->cookies[$key]["value"];
    } else {
      return null;
    }
  }
}
