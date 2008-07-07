<?php

/**
 * Settings of ACL.
 *
 * @category   Addon
 * @package    addon.acl
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Acl_Config implements Sabel_Config
{
  protected $configs = array();
  
  public function configure()
  {
    $index = $this->module("index");
    $index->allow();
    
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
