<?php

/**
 * Acl_Processor
 *
 * @version    1.0
 * @category   Addon
 * @package    addon.acl
 * @author     Mori Reo <mori.reo@gmail.com>
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Acl_Processor extends Sabel_Bus_Processor
{
  const DENY_ACTION = "forbidden";
  
  private
    $user    = null,
    $configs = array();
    
  public function execute($bus)
  {
    $config = new Acl_Config();
    $this->configs = $config->configure();
    
    $destination = $this->destination;
    $action = $destination->getAction();
    $this->user = new Acl_User();
    
    if ($this->storage->has("acl_user")) {
      $this->user->restore($this->storage->read("acl_user"));
    }
    
    $this->controller->setAttribute("user", $this->user);
    
    $module = $destination->getModule();
    $controller = $destination->getController();
    
    if (isset($this->configs[$module])) {
      $mConfig = $this->configs[$module];
      $cConfig = $mConfig->getController($controller);
      
      if ($cConfig === null) {
        if ($this->isAllow($mConfig)) return;
        $authUri = $mConfig->authUri();
      } else {
        if ($this->isAllow($cConfig)) return;
        $authUri = $cConfig->authUri();
        if ($authUri === null) $authUri = $mConfig->authUri();
      }
      
      $this->forbidden($authUri);
    } else {
      $this->forbidden();
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
  
  private function forbidden($authUri = null)
  {
    if (($redirector = $this->controller->redirect) && $authUri !== null) {
      $redirector->to($authUri);
    } else {
      $this->response->forbidden();
      $this->destination->setAction(self::DENY_ACTION);
    }
  }
}
