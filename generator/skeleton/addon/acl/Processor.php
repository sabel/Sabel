<?php

/**
 * Acl_Processor
 *
 * @category   Addon
 * @package    addon.acl
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Acl_Processor extends Sabel_Bus_Processor
{
  const DENY_ACTION = "forbidden";
  
  /**
   * @var Acl_User
   */
  private $user = null;
  
  public function execute($bus)
  {
    $config     = new Acl_Config();
    $configs    = $config->configure();
    $this->user = new Acl_User($bus->get("session"));
    $this->user->restore();
    
    $bus->get("controller")->setAttribute("aclUser", $this->user);
    
    $destination = $bus->get("destination");
    $module      = $destination->getModule();
    $controller  = $destination->getController();
    
    if (isset($configs[$module])) {
      $modConfig  = $configs[$module];
      $ctrlConfig = $modConfig->getController($controller);
      
      if ($ctrlConfig === null) {
        if ($this->isAllow($modConfig)) return;
        $authUri = $modConfig->authUri();
      } else {
        if ($this->isAllow($ctrlConfig)) return;
        $authUri = $ctrlConfig->authUri();
        
        if ($authUri === null) {
          $authUri = $modConfig->authUri();
        }
      }
      
      l("[acl] access denied.", SBL_LOG_DEBUG);
      
      if ($authUri === null) {
        $bus->get("response")->forbidden();
      } else {
        $bus->get("controller")->getRedirector()->to($authUri);
      }
    } else {
      l("[acl] access denied. (no module settings for '{$module}')", SBL_LOG_DEBUG);
      $bus->get("response")->forbidden();
    }
  }
  
  public function shutdown($bus)
  {
    $this->user->save();
  }
  
  private function isAllow($config)
  {
    if ($config->isPublic()) {
      return true;
    } elseif ($this->user->isAuthenticated()) {
      return $config->isAllow($this->user->role);
    } else {
      return false;
    }
  }
}
