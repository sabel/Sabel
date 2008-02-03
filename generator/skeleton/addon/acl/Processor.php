<?php

/**
 * Acl_Processor
 *
 * @version    1.0
 * @category   Addon
 * @package    addon.acl
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@sabel.jp>
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
    $this->user = new Acl_User();
    
    if ($aclUser = $bus->get("storage")->read("acl_user")) {
      $this->user->restore($aclUser);
    }
    
    $bus->get("controller")->setAttribute("user", $this->user);
    
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
        if ($authUri === null) $authUri = $modConfig->authUri();
      }
      
      if ($authUri === null) {
        $bus->get("response")->forbidden();
      } else {
        $bus->get("controller")->getRedirector()->to($authUri);
      }
    } else {
      $bus->get("response")->forbidden();
    }
  }
  
  public function shutdown($bus)
  {
    $bus->get("storage")->write("acl_user", $this->user->toArray());
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
