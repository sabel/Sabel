<?php

/**
 * Processor_Controller
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Controller extends Sabel_Bus_Processor
{
  const CONTROLLERS_DIR    = "controllers";
  const DEFAULT_CONTROLLER = "index";
  
  public function execute($bus)
  {
    $destination = $bus->get("destination");
    $response = new Sabel_Response_Web();
    
    try {
      $controller = $this->createController($response, $destination);
    } catch (Exception $e) {
      $module = $destination->getModule();
      l("can't create controller use default {$module}/index/index");
      
      $destination->setModule($module);
      $destination->setController("index");
      $destination->setAction("notFound");
      
      try {
        $controller = $this->createController($response, $destination);
      } catch (Exception $e) {
        $destination->setModule("index");
        $destination->setController("index");
        $destination->setAction("notFound");
        $controller = $this->createController($response, $destination);
      }
    }
    
    $controller->setup($bus->get("request"), $destination, $bus->get("storage"));
    $controller->setBus($bus);
    
    $bus->set("response",   $response);
    $bus->set("controller", $controller);
  }
  
  protected function createController($response, $destination)
  {
    list($module, $controller,) = $destination->toArray();
    $class = ucfirst($module) . "_" . ucfirst(self::CONTROLLERS_DIR);
    
    if ($controller !== "") {
      $class .= "_" . ucfirst($controller);
    } else {
      $class .= "_" . ucfirst(self::DEFAULT_CONTROLLER);
    }
    
    Sabel::using($class);
    
    if (class_exists($class, false)) {
      l("create controller '{$class}'");
      return new $class($response);
    } else {
      throw new Sabel_Exception_Runtime("controller not found.");
    }
  }
}
