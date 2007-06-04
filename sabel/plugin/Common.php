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
  public function onCreateController($destination)
  {
    $dest = $destination->toArray();
    list($m, $c,) = array_map("ucfirst", $dest);
    
    $flowClass = $m."_Flow_".$c;
    if (class_exists($flowClass)) {
      Sabel_Plugin::create()->add(new Sabel_Plugin_Flow());
    }
  }
}
