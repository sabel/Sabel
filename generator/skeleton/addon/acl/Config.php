<?php

/**
 * Settings for Acl.
 *
 * @category   Addon
 * @package    addon.acl
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Acl_Config implements Sabel_Config
{
  protected $configs = array();
  
  public function configure()
  {
    // $this->module("index")->allow();
    
    return $this->configs;
  }
  
  protected function module($module)
  {
    if (!is_string($module) || $module === "") {
      $message = "must specify module name.";
      throw new Sabel_Exception_InvalidArgument($message);
    } else {
      return $this->configs[$module] = new Acl_Config_Module($module);
    }
  }
}
