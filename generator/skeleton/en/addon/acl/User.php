<?php

/**
 * Acl_User
 *
 * @category   Addon
 * @package    addon.acl
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Acl_User
{
  const AUTHED_KEY  = "authenticated";
  const SESSION_KEY = "sbl_acl_user";
  
  /**
   * @var Sabel_Session_Abstract
   */
  private $session = null;
  
  /**
   * @var Sabel_Controller_Redirector
   */
  private $redirector = null;
  
  /**
   * @var array
   */
  private $attributes = array();
  
  public function __construct(Sabel_Session_Abstract $session)
  {
    $this->session = $session;
  }
  
  public function setRedirector(Sabel_Controller_Redirector $redirector)
  {
    $this->redirector = $redirector;
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
    
    if ($regenerateId) $this->session->regenerateId();
  }
  
  public function deAuthenticate()
  {
    $this->attributes = array(self::AUTHED_KEY => false);
  }
  
  public function login($redirectTo)
  {
    $roles = func_get_args();
    array_shift($roles);
    
    if (($uri = $this->session->read("acl_after_auth_uri")) !== null) {
      $this->redirector->uri($uri);
    } else {
      $this->redirector->to($redirectTo);
    }
    
    $this->authenticate($roles[0]);
    
    if (($c = count($roles)) > 1) {
      for ($i = 1; $i < $c; $i++) {
        $this->addRole($roles[$i]);
      }
    }
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
  
  public function hasRole($role)
  {
    $roles = $this->__get("role");
    
    if (is_array($roles)) {
      return in_array($role, $roles, true);
    } else {
      return false;
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
