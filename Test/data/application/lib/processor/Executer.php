<?php

/**
 * Processor_Executer
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Executer extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $injector = Sabel_Container::injector(new Factory());
    
    $controller  = $bus->get("controller");
    $request     = $bus->get("request");
    $destination = $bus->get("destination");
    $storage     = $bus->get("storage");
    $action      = $destination->getAction();
    
    $controller->setAction($action);
    $controller->initialize();
    
    if (method_exists($controller, $action)) {
      $annotation = new Sabel_Annotation_ReflectionClass(get_class($controller));
      $method = $annotation->getMethod($action);
      if ($method->getAnnotation("post") === "only" && !$request->isPost()) {
        $bus->set("response", $controller->getResponse()->notFound());
        return true;
      }
    }
    
    $response = $controller->execute($action);
    $bus->set("response", $response);
    
    return true;
  }
}
