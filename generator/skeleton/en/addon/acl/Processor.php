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
  /**
   * @var Acl_User
   */
  protected $user = null;
  
  public function execute($bus)
  {
    $config  = new Acl_Config();
    $configs = $config->configure();
    $session = $bus->get("session");
    
    $this->user = new Acl_User($session);
    $this->user->restore();
    
    $controller = $bus->get("controller");
    $controller->setAttribute("aclUser", $this->user);
    
    $destination = $bus->get("destination");
    $module = $destination->getModule();
    
    if (isset($configs[$module])) {
      $modConfig  = $configs[$module];
      $ctrlConfig = $modConfig->getController($destination->getController());
      
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
      
      l("[ACL] access denied.", SBL_LOG_DEBUG);
      
      if ($authUri === null) {
        $bus->get("response")->getStatus()->setCode(Sabel_Response::FORBIDDEN);
      } else {
        $session->write("acl_after_auth_uri", $bus->get("request")->getUri(), 180);
        $bus->get("redirector")->to($authUri);
      }
    } else {
      l("[ACL] access denied. (no module settings for '{$module}')", SBL_LOG_DEBUG);
      $bus->get("response")->getStatus()->setCode(Sabel_Response::FORBIDDEN);
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
