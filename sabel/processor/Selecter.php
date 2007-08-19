<?php

/**
 * Sabel_Processor_Selecter
 *
 * @category   Processor
 * @package    org.sabel.processor
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Processor_Selecter extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $name = get_class($bus->get("controller"));
    $reflect = new Sabel_Annotation_ReflectionClass($name);
    
    if ($reflect->hasAnnotation("executer")) {
      $executer = $reflect->getAnnotation("executer");
      if ($executer === "flow") {
        $flow = new Sabel_Processor_Flow("flow");
        $bus->getProcessor("executer")->get("executer")->replace($flow);
      }
    }
  }
}
