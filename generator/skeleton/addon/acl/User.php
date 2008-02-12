<?php

/**
 * Acl_User
 *
 * @category   Addon
 * @package    addon.acl
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2002-2006 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Acl_User
{
  const AUTHED_KEY  = "authenticated";
  const SESSION_KEY = "sbl_acl_user";
  
  private $session = null;
  private $attributes = array();
  
  public function __construct(Sabel_Session_Abstract $session)
  {
    $this->session = $session;
  }
  
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
  
  public function getSessionId()
  {
    return $this->session->getId();
  }
  
  public function toArray()
  {
    return $this->attributes;
  }
  
  public function restore()
  {
    if ($attributes = $this->session->read(self::SESSION_KEY)) {
      $this->attributes = $attributes;
    }
  }
  
  public function save()
  {
    $this->session->write(self::SESSION_KEY, $this->attributes);
  }
  
  public function isAuthenticated()
  {
    $attr = $this->attributes;
    return (isset($attr[self::AUTHED_KEY]) && $attr[self::AUTHED_KEY]);
  }
  
  public function authenticate($role, $regenerateId = true)
  {
    $this->attributes[self::AUTHED_KEY] = true;
    $this->addRole($role);
    
    if ($regenerateId) {
      $this->session->regenerateId(true);
    }
  }
  
  public function deAuthenticate()
  {
    $this->attributes = array(self::AUTHED_KEY => false);
  }
  
  public function addRole($add)
  {
    $role = $this->__get("role");
    
    if ($role === null) {
      $this->attributes["role"] = array($add);
    } elseif (!in_array($add, $role, true)) {
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
}
