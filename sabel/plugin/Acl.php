<?php

/**
 * Sabel_Plugin_Acl
 *
 * @category   Plugin
 * @package    org.sabel.controller.executer
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Plugin_Acl extends Sabel_Plugin_Base
{
  const ACL_LOGIN_KEY = "acl_login_id";
  const DENY_ACTION   = "accessDeny";
  
  const RULE_DENY  = "deny";
  const RULE_ALLOW = "allow";
  
  const ALLOW_ALL = "allow_all";
  const DENY_ALL  = "deny_all";
  
  private $rule = null;
  
  public function __construct($defualtRule = null)
  {
    if ($defualtRule === null) {
      $this->rule = self::RULE_DENY;
    } else {
      $this->rule = self::RULE_ALLOW;
    }
  }
  
  /**
   * execute an action.
   * overwrite parent executeAction method.
   *
   * @param string $action
   */
  public function onExecuteAction($action)
  {
    $privateActions = "aclPrivateActions";
    $publicActions  = "aclPublicActions";
    
    if ($this->rule === self::RULE_DENY) {
      if (method_exists($this->controller, $privateActions)) {
        throw new Sabel_Exception_Runtime("duplicate double deny");
      }
      
      if ($action === "notFound" || $action === "serverError") {
        return $this->controller->execute($action);
      }
      
      if (method_exists($this->controller, $publicActions)) {
        $result = $this->controller->$publicActions();
        if ($result === self::ALLOW_ALL) {
          return $this->controller->execute($action);
        } elseif ($result === self::DENY_ALL) {
          throw new Sabel_Exception_Runtime("duplicate double deny");
        }
        if (in_array($action, $result)) {
          return $this->controller->execute($action);
        } elseif ($this->isAuthenticated()) {
          return $this->controller->execute($action);
        } else {
          if ($this->controller->executable($action)) {
            $this->destination->setAction(self::DENY_ACTION);
            return $this->controller->execute(self::DENY_ACTION);
          } else {
            return $this->controller->execute($action);
          }
        }
      } elseif ($this->isAuthenticated()) {
        return $this->controller->execute($action);
      } else {
        $this->destination->setAction(self::DENY_ACTION);
        return $this->controller->execute(self::DENY_ACTION);
      }
    } elseif ($this->rule === self::RULE_ALLOW) {
      if (method_exists($this->controller, $publicActions)) {
        throw new Sabel_Exception_Runtime("duplicate double allow");
      }
      
      if (method_exists($this->controller, $privateActions)) {
        $result = $this->controller->$privateActions();
        if ($result === self::DENY_ALL) {
          $this->destination->setAction(self::DENY_ACTION);
          return $this->controller->execute(self::DENY_ACTION);
        } elseif ($result === self::ALLOW_ALL) {
          throw new Sabel_Exception_Runtime("duplicate double allow");
        }
        if (in_array($action, $result)) {
          if ($this->isAuthenticated()) {
            return $this->controller->execute($action);
          } else {
            $this->destination->setAction(self::DENY_ACTION);
            return $this->controller->execute(self::DENY_ACTION);
          }
        } else {
          return $this->controller->execute($action);
        }
      } else {
        return $this->controller->execute($action);
      }
    }
    
    
  }
  
  public function authenticate($authentication)
  {
    if ($authentication->authenticate()) {
      $identity = $authentication->fetchIdentity();
      $this->controller->getStorage()->write(self::ACL_LOGIN_KEY, $identity);
      return true;
    } else {
      return false;
    }
  }
  
  public function unAuthenticate()
  {
    $this->controller->getStorage()->delete(self::ACL_LOGIN_KEY);
  }
  
  public function identity()
  {
    return $this->controller->getStorage()->read(self::ACL_LOGIN_KEY);
  }
  
  public function isAuthenticated()
  {
    return ($this->controller->getStorage()->has(self::ACL_LOGIN_KEY));
  }
  
  public function allowAll()
  {
    return self::ALLOW_ALL;
  }
  
  public function denyAll()
  {
    return self::DENY_ALL;
  }
}
