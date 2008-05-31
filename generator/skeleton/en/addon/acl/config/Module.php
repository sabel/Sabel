<?php

/**
 * Acl_Config_Module
 *
 * @category   Addon
 * @package    addon.acl
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Acl_Config_Module extends Acl_Config_Controller
{
  protected $controllers = array();
  
  public function controller($controller)
  {
    return $this->controllers[$controller] = new Acl_Config_Controller();
  }
  
  public function controllers(/* args */)
  {
    $args = func_get_args();
    $controllers = array();
    
    foreach ($args as $c) {
      $controllers[$c] = new Acl_Config_Controller();
    }
    
    $this->controllers = array_merge($this->controllers, $controllers);
    return new Acl_Config_Controllers($controllers);
  }
  
  public function getController($controller)
  {
    if (isset($this->controllers[$controller])) {
      return $this->controllers[$controller];
    } else {
      return null;
    }
  }
}
