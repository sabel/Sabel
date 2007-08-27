<?php

/**
 * Processor_Acl
 *
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
    $this->storage = $storage = $bus->get("storage");
    
    $controller  = $bus->get("controller");
    $destination = $bus->get("destination");
    $this->user  = new Processor_Acl_User();
    $action = $destination->getAction();
    
    if ($storage->has("acl_user")) {
      $this->user->restore($storage->read("acl_user"));
    }
    
    $controller->user = $this->user;
    $privateActions   = "aclPrivateActions";
    $publicActions    = "aclPublicActions";
    
    if ($this->rule === self::RULE_DENY) {
      if (method_exists($controller, $privateActions)) {
        throw new Sabel_Exception_Runtime("duplicate double deny");
      }
      
      if (!$this->user->isAuthenticated()) {
        if (method_exists($controller, $publicActions)) {
          $pubActions = $controller->$publicActions();
          
          if ($pubActions === self::DENY_ALL) {
            throw new Sabel_Exception_Runtime("duplicate double deny");
          }
          
          $found = false;
          foreach ($pubActions as $publicAction) {
            if (fnmatch($publicAction, $action)) {
              $found = true;
              break;
            }
          }
          
          if (!$found && $controller->executable($action)) {
            $destination->setAction(self::DENY_ACTION);
          }
        } else {
          $destination->setAction(self::DENY_ACTION);
        }
      }
    } elseif ($this->rule === self::RULE_ALLOW) {
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
    
    return new Sabel_Bus_ProcessorCallback($this, "onAfterAction", "executer");
  }
  
  public function onAfterAction()
  {
    $this->storage->write("acl_user", $this->user->toArray());
  }
}
