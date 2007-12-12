<?php

/**
 * Acl_Processor
 *
 * @version    1.0
 * @category   Addon
 * @package    addon.acl
 * @author     Mori Reo <mori.reo@gmail.com>
 *             Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Acl_Processor extends Sabel_Bus_Processor
{
  const DENY_ACTION = "notFound";
  const RULE_DENY   = "deny";
  const RULE_ALLOW  = "allow";
  
  private $user = null;
  
  /**
   * execute an action.
   * overwrite parent executeAction method.
   *
   * @param string $action
   */
  public function execute($bus)
  {
    $action = $this->destination->getAction();
    $this->user = new Acl_User();
    
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
  }
  
  public function shutdown()
  {
    $this->storage->write("acl_user", $this->user->toArray());
  }
  
  private function processDefaultDeny($controller, $action)
  {
    if (!$this->user->isAuthenticated($this->getRole())) {
      $publicActions = $this->getAclActions("public");
      if (!$this->isActionFound($publicActions, $action)) {
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
  
  private function getRole()
  {
    if ($this->reflection->hasAnnotation("role")) {
      $role = $this->reflection->getAnnotation("role");
      return $role[0][0];
    } else {
      return null;
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
