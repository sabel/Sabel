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
  const DENY_ACTION = "notFound";
  const RULE_DENY   = "deny";
  const RULE_ALLOW  = "allow";
  
  private $user        = null;
  private $controller  = null;
  private $destination = null;
  private $reflection  = null;
  
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
    
    $this->controller->setAttribute("user", $this->user);
    $this->reflection = new Sabel_Annotation_ReflectionClass($this->controller);
    
    if ($this->reflection->hasAnnotation("default")) {
      $default = $this->reflection->getAnnotation("default");
      $default = $default[0][0];
    } else {
      $default = self::RULE_DENY;
    }
    
    if ($default === self::RULE_DENY) {
      $this->processDefaultDeny($this->controller, $action);
    } else {
      $this->processDefaultAllow($this->controller, $action);
    }
    
    return new Sabel_Bus_ProcessorCallback($this, "onAfterAction", "executer");
  }
  
  private function processDefaultDeny($controller, $action)
  {
    if (!$this->user->isAuthenticated($this->getRole())) {
      $publicActions = $this->getAclActions("public");
      $found = $this->isActionFound($publicActions, $action);
      
      if (!$found && $this->controller->executable($action)) {
        $this->destination->setAction(self::DENY_ACTION);
      }
    }
  }
  
  private function processDefaultAllow($controller, $action)
  {
    if (!$this->user->isAuthenticated($this->getRole())) {
      $privateActions = $this->getAclActions("private");
      if ($this->isActionFound($privateActions, $action)) {
        $this->destination->setAction(self::DENY_ACTION);
      }
    }
  }
  
  public function onAfterAction()
  {
    $this->storage->write("acl_user", $this->user->toArray());
  }
  
  private function getRole()
  {
    if ($this->reflection->hasAnnotation("role")) {
      $role = $this->reflection->getAnnotation("role");
      return $role[0][0];
    } else {
      return "default";
    }
  }
  
  private function getAclActions($type)
  {
    if ($this->reflection->hasAnnotation($type)) {
      $actions = $this->reflection->getAnnotation($type);
      return $actions[0];
    } else {
      return array();
    }
  }
  
  private function isActionFound($actions, $targetAction)
  {
    if (empty($actions)) return false;
    
    foreach ($actions as $action) {
      if (fnmatch($action, $targetAction)) {
        return true;
      }
    }
    
    return false;
  }
}
