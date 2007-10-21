<?php

/**
 * Processor_Selecter
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Selecter extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $controller = $bus->get("controller");
    $reflection = $controller->getReflection();
    
    $annot = $reflection->getAnnotation("executer");
    if ($annot === null || $annot[0][0] !== "flow") return true;
    
    $flow = new Processor_Flow("executer");
    $redirecter = new Processor_Flow_Redirecter("redirecter");
    $bus->getList()->find("executer")->replace($flow);
    $bus->getList()->find("redirecter")->replace($redirecter);
  }
}
