<?php

class Sabel_Processor_Acl implements Sabel_Bus_Processor
{
  const ACL_LOGIN_KEY = "acl_login_id";
  const DENY_ACTION   = "accessDeny";
  
  const RULE_DENY  = "deny";
  const RULE_ALLOW = "allow";
  
  const ALLOW_ALL = "allow_all";
  const DENY_ALL  = "deny_all";
  
  private $rule = null;
  private $user = null;
  
  public function execute($bus)
  {
    $this->rule = self::RULE_DENY;
    
    $this->user = new Sabel_Plugin_Acl_User();
    
    $storage     = $bus->get("storage");
    $controller  = $bus->get("controller");
    $request     = $bus->get("request");
    $destination = $bus->get("destination");
    
    $action = $destination->getAction();
    
    if ($storage->has("acl_user")) {
      $this->user->restore($storage->read("acl_user"));
    }
    
    $request->setVariable("user", $this->user);
    
    $privateActions = "aclPrivateActions";
    $publicActions  = "aclPublicActions";
    
    if ($this->rule === self::RULE_DENY) {
      if (method_exists($controller, $privateActions)) {
        throw new Sabel_Exception_Runtime("duplicate double deny");
      }
      
      if ($action === "notFound" || $action === "serverError") {
        return $controller->execute($action);
      }
      
      if (method_exists($controller, $publicActions)) {
        $pubActions = $controller->$publicActions();
        if ($pubActions === self::ALLOW_ALL) {
          return $controller->execute($action);
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
          return $controller->execute($action);
        } elseif ($this->user->isAuthenticated()) {
          return $controller->execute($action);
        } else {
          if ($controller->executable($action)) {
            $destination->setAction(self::DENY_ACTION);
            return $controller->execute(self::DENY_ACTION);
          } else {
            return $controller->execute($action);
          }
        }
      } elseif ($this->user->isAuthenticated()) {
        return $controller->execute($action);
      } else {
        $destination->setAction(self::DENY_ACTION);
        return $controller->execute(self::DENY_ACTION);
      }
    } elseif ($this->rule === self::RULE_ALLOW) {
      if (method_exists($controller, $publicActions)) {
        throw new Sabel_Exception_Runtime("duplicate double allow");
      }
      
      if (method_exists($controller, $privateActions)) {
        $priActions = $controller->$privateActions();
        if ($priActions === self::DENY_ALL) {
          $destination->setAction(self::DENY_ACTION);
          return $controller->execute(self::DENY_ACTION);
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
            return $controller->execute($action);
          } else {
            $destination->setAction(self::DENY_ACTION);
            return $controller->execute(self::DENY_ACTION);
          }
        } else {
          return $controller->execute($action);
        }
      } else {
        return $controller->execute($action);
      }
    }
    
    $storage->write("acl_user", $this->user->toArray());
  }
}
