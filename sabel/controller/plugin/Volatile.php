<?php

/**
 * Volatile plugin
 *
 * @category   Controller
 * @package    org.sabel.controller.plugin
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Controller_Plugin_Volatile implements Sabel_Controller_Page_Plugin
{
  protected $volatiles = array();
  
  public function volatile($key, $value)
  {
    $this->volatiles[$key] = $value;
  }
  
  public function onBeforeAction($controller)
  {
    $storage = Sabel_Storage_Session::create();
    
    if (is_array($storage->read("volatiles"))) {
      $attributes = array_merge($storage->read("volatiles"), $controller->getAttributes());
      $controller->setAttributes($attributes);
      foreach ($storage->read("volatiles") as $vname => $vvalue) {
        $storage->delete($vname);
      }
    }
  }
  
  public function onAfterAction($controller)
  {
    $storage = Sabel_Storage_Session::create();
    $storage->write("volatiles", $this->volatiles);
  }
}