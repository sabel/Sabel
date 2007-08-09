<?php

/**
 * Sabel_Controller_Front
 *
 * @category   Controller
 * @package    org.sabel.controller
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
final class Sabel_Controller_Front
{
  public function ignition($request = null, $storage = null)
  {
    $bus = new Sabel_Bus();
    $bus->init(array("storage" => $storage, "request" => $request));
    
    $bus->addGroup("request",  new Sabel_Processor_Request());
    $bus->addGroup("router",   new Sabel_Processor_Router());
    $bus->addGroup("helper",   new Sabel_Processor_Helper());
    $bus->addGroup("creator",  new Sabel_Processor_Creator());
    $bus->addGroup("executer", new Sabel_Processor_Executer());
    $bus->addGroup("response", new Sabel_Processor_Response());
    $bus->addGroup("renderer", new Sabel_Processor_Renderer());
    
    return $bus->run();
  }
}
