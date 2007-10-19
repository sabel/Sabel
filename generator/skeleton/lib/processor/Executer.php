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
    $controller  = $bus->get("controller");
    $request     = $bus->get("request");
    $destination = $bus->get("destination");
    $response    = $controller->getResponse();
    
    $action = $destination->getAction();
    $controller->setAction($action);
    $controller->initialize();
    
    if ($response->isNotFound()) {
      $bus->set("response", $response);
    } elseif (method_exists($controller, $action)) {
      $annotation = new Sabel_Annotation_ReflectionClass(get_class($controller));
      $method = $annotation->getMethod($action);
      $annot = $method->getAnnotation("post");
      if ($annot[0][0] === "only" && !$request->isPost()) {
        $bus->set("response", $response->notFound());
      } else {
        $bus->set("response", $controller->execute($action));
      }
    } else {
      $bus->set("response", $response->notFound());
    }
    
    return true;
  }
}
