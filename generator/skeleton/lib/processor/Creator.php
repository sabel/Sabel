<?php

/**
 * Processor_Creator
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Creator extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $injector = Sabel_Container::create(new Config_Factory());
    $destination = $this->destination;
    $creator = new Sabel_Controller_Creator();
    
    try {
      $controller = $creator->create($destination);
    } catch (Exception $e) {
      $module = $destination->getModule();
      l("can't create controller use default {$module}/index/index");
      $destination->setModule($module);
      $destination->setController("index");
      $destination->setAction("notFound");
      try {
        $controller = $creator->create($destination);
      } catch (Exception $e) {
        $destination->setModule("index");
        $destination->setController("index");
        $destination->setAction("notFound");
        $controller = $creator->create($destination);
      }
    }
    
    $controller->setup($this->request, $destination, $this->storage);
    $controller->setBus($bus);
    $bus->set("controller", $controller);
  }
}
