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
class Acl_User extends Sabel_ValueObject
{
  const URI_HISTORY_COUNT = 5;
  
  const AUTHED_KEY      = "authenticated";
  const SESSION_KEY     = "sbl_acl_user";
  const URI_HISTORY_KEY = "sbl_acl_uri_history";
  
  /**
   * @var Sabel_Session_Abstract
   */
  private $session = null;
  
  /**
   * @var Sabel_Redirector
   */
  private $redirector = null;
  
  public function __construct()
  {
    $bus = Sabel_Context::getContext()->getBus();
    $this->redirector = $bus->get("redirector");
    
    $session = $bus->get("session");
    $request = $bus->get("request");
    
    $values = $session->read(self::SESSION_KEY);
    if ($values === null) $values= array();
    
    $history = array();
    if (isset($values[self::URI_HISTORY_KEY])) {
      $history = $values[self::URI_HISTORY_KEY];
    }
    
    if ($request->isGet()) {
      if (array_unshift($history, $request->getUri()) > self::URI_HISTORY_COUNT) {
        array_pop($history);
      }
    }
    
    $values[self::URI_HISTORY_KEY] = $history;
    $this->values  = $values;
    $this->session = $session;
  }
  
  public function getUriHistory()
  {
    return $this->__get(self::URI_HISTORY_KEY);
  }
  
  public function save()
  {
    $this->session->write(self::SESSION_KEY, $this->values);
  }
  
  public function isAuthenticated()
  {
    $v = $this->values;
    return (isset($v[self::AUTHED_KEY]) && $v[self::AUTHED_KEY]);
  }
  
  public function authenticate($role, $regenerateId = true)
  {
    $this->values[self::AUTHED_KEY] = true;
    $this->addRole($role);
    
    if ($regenerateId) $this->session->regenerateId();
  }
  
  public function deAuthenticate()
  {
    $this->values = array(self::AUTHED_KEY => false);
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
      $this->values["role"] = array($add);
    } elseif (!in_array($add, $role, true)) {
      $role[] = $add;
      $this->values["role"] = $role;
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
      $this->values["role"] = $role;
    }
  }
}
