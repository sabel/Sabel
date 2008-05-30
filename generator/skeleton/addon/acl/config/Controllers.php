<?php

/**
 * Acl_Config_Controllers
 *
 * @category   Addon
 * @package    addon.acl
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Acl_Config_Controllers extends Sabel_Object
{
  /**
   * @var Acl_Config_Controller[]
   */
  protected $controllers = array();
  
  public function __construct(array $controllers)
  {
    $this->controllers = $controllers;
  }
  
  public function allow($role = null)
  {
    foreach ($this->controllers as $controller) {
      $controller->allow($role);
    }
    
    return $this;
  }
  
  public function authUri($uri)
  {
    foreach ($this->controllers as $controller) {
      $controller->authUri($uri);
    }
    
    return $this;
  }
}
