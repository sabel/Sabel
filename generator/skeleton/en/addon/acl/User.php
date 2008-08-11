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
  
  const URI_HISTORY_COUNT = 5;
  const URI_HISTORY_KEY   = "sbl_acl_uri_history";
  
  /**
   * @var Sabel_Session_Abstract
   */
  private $session = null;
  
  /**
   * @var Sabel_Redirector
   */
  private $redirector = null;
  
  /**
   * @var array
   */
  private $attributes = array();
  
  public function __construct()
  {
    $bus = Sabel_Context::getContext()->getBus();
    $this->redirector = $bus->get("redirector");
    
    $session = $bus->get("session");
    $request = $bus->get("request");
    
    if (($history = $session->read(self::URI_HISTORY_KEY)) === null) {
      $history = array();
    }
    
    if ($request->isGet()) {
      if (array_unshift($history, $request->getUri()) > self::URI_HISTORY_COUNT) {
        array_pop($history);
      }
    }
    
    $session->write(self::URI_HISTORY_KEY, $history);
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
    
    $history  = $this->session->read(self::URI_HISTORY_KEY);
    $authUri  = $this->__get("__auth_uri");
    $loginUri = $history[0];
    $prevUri  = null;
    
    for ($i = 1; $i < self::URI_HISTORY_COUNT; $i++) {
      if ($history[$i] !== $loginUri && $history[$i] !== $authUri) {
        $prevUri = $history[$i];
        break;
      }
    }
    
    if ($authUri === null || $prevUri === null) {
      
      $this->redirector->to($redirectTo);
    } else {
      l("[ACL] back to the page before authentication.", SBL_LOG_DEBUG);
      $this->redirector->uri($prevUri);
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
