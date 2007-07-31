<?php

/**
 * Sabel_Controller_Plugin_Common
 *
 * @category   Controller
 * @package    org.sabel.controller.executer
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Plugin_Common extends Sabel_Plugin_Base
{
  private $context = null;
  
  public function plugin($plugin)
  {
    $this->context = Sabel_Context::getContext();
    $this->context->getPlugin()->add($plugin);
  }
  
  public function onCreateController($destination)
  {
    $dest = $destination->toArray();
    list($m, $c,) = array_map("ucfirst", $dest);
    
    $flowClass = $m."_Flow_".$c;
    if (class_exists($flowClass)) {
      $this->plugin(new Sabel_Plugin_Flow());
    }
  }
  
  public function onBeforeAction()
  {
    $module = Sabel_Context::getContext()->getDestination()->getModule();
    Sabel::fileUsing(RUN_BASE . "/app/initialize.php");
    Sabel::fileUsing(RUN_BASE . "/app/{$module}/initialize.php");
  }
}
