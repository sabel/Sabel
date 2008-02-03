<?php

/**
 * Acl_Config_Module
 *
 * @category   Addon
 * @package    addon.acl
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Acl_Config_Module extends Acl_Config_Abstract
{
  protected
    $controllers = array();
    
  public function controller($controller)
  {
    return $this->controllers[$controller] = new Acl_Config_Controller();
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
