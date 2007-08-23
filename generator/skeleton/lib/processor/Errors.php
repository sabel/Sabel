<?php

/**
 * Processor_Errors
 *
 * @category   Processor
 * @package    processor
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Errors extends Sabel_Bus_Processor
{
  const MAX_STACK_SIZE = 5;
  const ERROR_KEY      = "errors";
  const STACK_KEY      = "stack";
  
  private $ignoreUrls  = array();
  private $storage     = null;
  private $request     = null;
  
  public function resetErrors()
  {
    $this->storage->delete(self::ERROR_KEY);
    $this->storage->delete(self::STACK_KEY);
  }
  
  public function execute($bus)
  {
    $this->storage    = $storage    = $bus->get("storage");
    $this->request    = $request    = $bus->get("request");
    $this->controller = $controller = $bus->get("controller");
    
    $current = $request->getUri()->__toString();
    $errors  = $storage->read(self::ERROR_KEY);
    $ignore  = (in_array($current, $this->ignoreUrls) ||
                $request->isTypeOf("css") ||
                $request->isTypeOf("js"));
    
    if (is_array($errors)) {
      if ($current === $errors["submitUri"]) {
        $this->controller->setAttribute(self::ERROR_KEY, $errors["messages"]);
        $this->controller->setAttributes($errors["values"]);
        
        $models = array();
        foreach ($errors["values"] as $key => $value) {
          if (strpos($key, "::") !== false) {
            list ($mdlName, $colName) = explode("::", $key);
            if (!isset($models[$mdlName])) {
              $models[$mdlName] = MODEL($mdlName);
            }
            
            $models[$mdlName]->$colName = $value;
          }
        }
        
        foreach ($models as $mdlName => $model) {
          $mdlName{0} = strtolower($mdlName{0});
          $controller->setAttribute("{$mdlName}Form", new Helpers_Form($model));
        }
      } elseif (!$ignore && !$request->isPost()) {
        $storage->delete(self::ERROR_KEY);
      }
    }
    
    if (!$ignore) {
      $stack = $this->storage->read(self::STACK_KEY);
      
      if (is_array($stack)) {
        $stack[] = $current;
        if (count($stack) > self::MAX_STACK_SIZE) array_shift($stack);
      } else {
        $stack = array($current);
      }
      
      $this->storage->write(self::STACK_KEY, $stack);
    }
  }
  
  public function event($bus, $processor, $method, $result)
  {
    if ($processor->name === "redirecter" && $method === "onRedirect") {
      $controller = $bus->get("controller");
      $storage    = $bus->get("storage");
      $request    = $bus->get("request");

      if (($messages = $controller->errors) === null) return;

      $stack  = $storage->read(self::STACK_KEY);
      $index  = count($stack) - 1;
      $values = $request->fetchPostValues();

      $storage->write(self::ERROR_KEY, array("submitUri" => $stack[$index],
                                             "messages"  => $messages,
                                             "values"    => $values));
    }
  }
}
