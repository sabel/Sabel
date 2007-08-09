<?php

/**
 * Sabel_Processor_ExecuteException
 *
 * @category   Processor
 * @package    org.sabel.processor
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Processor_ExecuteException implements Sabel_Bus_Processor
{
  private $processor = null;
  
  public function __construct($processor)
  {
    $this->processor = $processor;
  }
  
  public function execute($bus)
  {
    try {
      $this->processor->execute($bus);
    } catch (Sabel_Exception_Runtime $e) {
      $injector    = $bus->get("injector");
      $request     = $bus->get("request");
      $destination = $bus->get("destination");
      $storage     = $bus->get("storage");
      
      $destination->setModule("index");
      $destination->setController("index");
      $destination->setAction("notFound");
      
      $creator = new Sabel_Controller_Creator();
      $controller = $creator->create($destination);
      
      $bus->set("controller", $controller);
      $bus->set("response",   $this->executeAction($bus));
    }
  }
  
  public function executeAction($bus)
  {
    return $this->processor->executeAction($bus);
  }
}
