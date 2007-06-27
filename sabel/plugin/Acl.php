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
  
  private $user = null;
  
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
    $this->user = new Sabel_Plugin_Acl_User();
    
    $storage = $this->controller->getStorage();
    if ($storage->has("acl_user")) {
      $this->user->restore($storage->read("acl_user"));
    }
    
    $this->controller->getContext()->getView()->assign("user", $this->user);
    $this->controller->getRequest()->setVariable("user", $this->user);
    
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
        $pubActions = $this->controller->$publicActions();
        if ($pubActions === self::ALLOW_ALL) {
          return $this->controller->execute($action);
        } elseif ($pubActions === self::DENY_ALL) {
          throw new Sabel_Exception_Runtime("duplicate double deny");
        }
        
        $found = false;
        foreach ($pubActions as $publicAction) {
          if (fnmatch($publicAction, $action)) {
            $found = true;
            break;
          }
        }
        
        if ($found) {
          return $this->controller->execute($action);
        } elseif ($this->user->isAuthenticated()) {
          return $this->controller->execute($action);
        } else {
          if ($this->controller->executable($action)) {
            $this->destination->setAction(self::DENY_ACTION);
            return $this->controller->execute(self::DENY_ACTION);
          } else {
            return $this->controller->execute($action);
          }
        }
      } elseif ($this->user->isAuthenticated()) {
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
        $priActions = $this->controller->$privateActions();
        if ($priActions === self::DENY_ALL) {
          $this->destination->setAction(self::DENY_ACTION);
          return $this->controller->execute(self::DENY_ACTION);
        } elseif ($priActions === self::ALLOW_ALL) {
          throw new Sabel_Exception_Runtime("duplicate double allow");
        }
        
        $found = false;
        foreach ($priActions as $privateAction) {
          if (fnmatch($privateAction, $action)) {
            $found = true;
            break;
          }
        }
        
        if ($found) {
          if ($this->user->isAuthenticated()) {
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
  
  public function onAfterAction()
  {
    $storage = $this->controller->getStorage();
    $storage->write("acl_user", $this->user->toArray());
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
