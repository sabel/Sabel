<?php

/**
 * Processor_Acl
 *
 * @version    1.0
 * @category   Processor
 * @package    lib.processor
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Acl extends Sabel_Bus_Processor
{
  const ACL_LOGIN_KEY = "acl_login_id";
  const DENY_ACTION   = "deny";
  const RULE_DENY     = "deny";
  const RULE_ALLOW    = "allow";
  const ALLOW_ALL     = "allow_all";
  const DENY_ALL      = "deny_all";
  
  private $rule = null;
  private $user = null;
  
  private $controller = null;
  private $destination = null;
  
  private $reflection = null;
  
  // private $privateActions = "aclPrivateActions";
  // private $publicActions  = "aclPublicActions";
    
  public function __construct($name, $defualtRule = self::RULE_DENY)
  {
    parent::__construct($name);
    $this->rule = $defualtRule;
  }
  
  /**
   * execute an action.
   * overwrite parent executeAction method.
   *
   * @param string $action
   */
  public function execute($bus)
  {
    $this->storage     = $bus->get("storage");
    $this->controller  = $bus->get("controller");
    $this->destination = $bus->get("destination");
    
    $action = $this->destination->getAction();
    
    $this->user = new Processor_Acl_User();
    
    if ($this->storage->has("acl_user")) {
      $this->user->restore($this->storage->read("acl_user"));
    }
    
    $this->controller->user = $this->user;
    
    $this->reflection = new Sabel_Annotation_ReflectionClass($this->controller);
    
    if ($this->reflection->hasAnnotation("default")) {
      $default = $this->reflection->getAnnotation("default");
      $default = $default[0][0];
    } else {
      $default = self::RULE_DENY;
    }
    
    if ($default === self::RULE_DENY) {
      $this->processDefaultDeny($action);
    } else {
      $this->processDefaultAllow($action);
    }
    
    return new Sabel_Bus_ProcessorCallback($this, "onAfterAction", "executer");
  }
  
  private function processDefaultDeny($action)
  {
    if ($this->reflection->hasAnnotation("public")) {
      $publicActions = $this->reflection->getAnnotation("public");
      $publicActions = $publicActions[0];
    } else {
      $publicActions = array();
    }
    
    if ($this->reflection->hasAnnotation("role")) {
      $role = $this->reflection->getAnnotation("role");
      $role = $role[0][0];
    } else {
      $role = "default";
    }
    
    if ($role === "default") {
      if (!$this->user->isAuthenticated()) {
        $found = false;

        if (count($publicActions) !== 0) {
          foreach ($publicActions as $publicAction) {
            if (fnmatch($publicAction, $action)) {
              $found = true;
              break;
            }
          }
        }

        if (!$found && $this->controller->executable($action)) {
          $this->destination->setAction(self::DENY_ACTION);
        }
      }
    } else {
      if (!$this->user->isAuthenticatedAs($role)) {
        $found = false;

        if (count($publicActions) !== 0) {
          foreach ($publicActions as $publicAction) {
            if (fnmatch($publicAction, $action)) {
              $found = true;
              break;
            }
          }
        }

        if (!$found && $this->controller->executable($action)) {
          $this->destination->setAction(self::DENY_ACTION);
        }
      }
    }
    

  }
  
  private function processDefaultAllow()
  {
    if (method_exists($controller, $publicActions)) {
      throw new Sabel_Exception_Runtime("duplicate double allow");
    }
    
    if (method_exists($controller, $privateActions)) {
      $priActions = $controller->$privateActions();
      if ($priActions === self::DENY_ALL) {
        $destination->setAction(self::DENY_ACTION);
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
        if (!$this->user->isAuthenticated()) {
          $destination->setAction(self::DENY_ACTION);
        }
      }
    }
  }
  
  public function onAfterAction()
  {
    $this->storage->write("acl_user", $this->user->toArray());
  }
}
