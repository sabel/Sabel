<?php

/**
 * Acl_User
 *
 * @version    1.0
 * @category   Addon
 * @package    addon.acl
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2002-2006 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Acl_User
{
  const AUTHED_KEY = "authenticated";
  
  private $attributes = array();
  
  public function __set($key, $value)
  {
    $this->attributes[$key] = $value;
  }
  
  public function __get($key)
  {
    if (array_key_exists($key, $this->attributes)) {
      return $this->attributes[$key];
    } else {
      return null;
    }
  }
  
  public function toArray()
  {
    return $this->attributes;
  }
  
  public function restore($attributes)
  {
    $this->attributes = $attributes;
  }
  
  public function authenticate($role)
  {
    $this->attributes[self::AUTHED_KEY] = true;
    $this->addRole($role);
  }
  
  public function deAuthenticate()
  {
    $this->destroy();
  }
  
  public function addRole($add)
  {
    $role = $this->__get("role");
    
    if ($role === null) {
      $this->attributes["role"] = array($add);
    } elseif (!in_array($add, $role)) {
      $role[] = $add;
      $this->attributes["role"] = $role;
    }
  }
  
  public function removeRole($remove)
  {
    $role = $this->__get("role");
    
    if (is_array($role)) {
      unset($role[$remove]);
      $this->attributes["role"] = $role;
    }
  }
  
  public function isAuthenticated()
  {
    $attr = $this->attributes;
    return (isset($attr[self::AUTHED_KEY]) && $attr[self::AUTHED_KEY]);
  }
  
  public function isTypeOf($compare)
  {
    if (!isset($this->attributes["type"])) return false;
    return ($this->attributes["type"] === $compare);
  }
  
  public function destroy()
  {
    $this->attributes = array();
    $this->attributes[self::AUTHED_KEY] = false;
  }
}
