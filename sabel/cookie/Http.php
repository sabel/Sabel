<?php

/**
 * Sabel_Cookie_Http
 *
 * @category   Cookie
 * @package    org.sabel.cookie
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Cookie_Http extends Sabel_Object
{
  public function set($key, $value, $options = array())
  {
    $options = $this->createOptions($options);
    
    setcookie($key,
              $value,
              $options["expire"],
              $options["path"],
              $options["domain"],
              $options["secure"],
              $options["httpOnly"]);
  }
  
  public function rawset($key, $value, $options = array())
  {
    $options = $this->createOptions($options);
    
    setrawcookie($key,
                 $value,
                 $options["expire"],
                 $options["path"],
                 $options["domain"],
                 $options["secure"],
                 $options["httpOnly"]);
  }
  
  public function get($key)
  {
    if (array_key_exists($key, $_COOKIE)) {
      return $_COOKIE[$key];
    } else {
      return null;
    }
  }
  
  public function delete($key, $options = array())
  {
    $options["expire"] = time() - 3600;
    $this->set($key, "", $options);
  }
  
  protected function createOptions(array $options)
  {
    $expire   = time() + 86400;
    $path     = "/";
    $domain   = Sabel_Environment::get("HTTP_HOST");
    $secure   = false;
    $httpOnly = false;
    
    if (!isset($options["expire"]))   $options["expire"]   = $expire;
    if (!isset($options["path"]))     $options["path"]     = $path;
    if (!isset($options["domain"]))   $options["domain"]   = $domain;
    if (!isset($options["secure"]))   $options["secure"]   = $secure;
    if (!isset($options["httpOnly"])) $options["httpOnly"] = $httpOnly;
    
    return $options;
  }
}
