<?php

/**
 * Processor_Errors
 *
 * @category   Processor
 * @package    processor
 * @author     Mori Reo <mori.reo@gmail.com>
 *             Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Errors extends Sabel_Bus_Processor
{
  const STACK_SIZE = 5;
  const ERROR_KEY  = "errors";
  const STACK_KEY  = "stack";
  
  public function execute($bus)
  {
    $storage    = $bus->get("storage");
    $request    = $bus->get("request");
    $controller = $bus->get("controller");
    $model      = $controller->getAttribute("model");
    $current    = $request->getUri()->__toString();
    $ignore     = ($request->isTypeOf("css") || $request->isTypeOf("js"));
    $errors     = $storage->read(self::ERROR_KEY);
    
    if (is_array($errors)) {
      if ($current === $errors["submitUri"]) {
        $controller->setAttribute(self::ERROR_KEY, $errors["messages"]);
        $controller->setAttributes($errors["values"]);
        
        if ($names = $this->getModelNames($errors["values"])) {
          $models = array();
          foreach ($names as $name) {
            $models[$name] = $model->createModel($name, $errors["values"]);
          }
          
          foreach ($models as $mdlName => $model) {
            $mdlName{0} = strtolower($mdlName{0});
            $controller->setAttribute("{$mdlName}Form", new ModelForm($model));
          }
        }
      } elseif (!$ignore && $request->isGet()) {
        $storage->delete(self::ERROR_KEY);
      }
    }
    
    if (!$ignore) {
      $stack = $storage->read(self::STACK_KEY);
      
      if (is_array($stack)) {
        $stack[] = $current;
        if (count($stack) > self::STACK_SIZE) array_shift($stack);
      } else {
        $stack = array($current);
      }
      
      $storage->write(self::STACK_KEY, $stack);
    }
  }
  
  public function event($bus, $processor, $method, $result)
  {
    if ($processor->name === "redirecter" && $method === "onRedirect") {
      $storage = $bus->get("storage");
      if (($messages = $bus->get("controller")->errors) === null) {
        $this->resetErrors($storage);
      } else {
        $stack  = $storage->read(self::STACK_KEY);
        $index  = count($stack) - 1;
        $values = $bus->get("request")->fetchPostValues();
        $errors = array("submitUri" => $stack[$index],
                        "messages"  => $messages,
                        "values"    => $values);
                        
        $storage->write(self::ERROR_KEY, $errors);
      }
    }
  }
  
  protected function getModelNames($values)
  {
    $names = array();
    foreach ($values as $key => $value) {
      if (strpos($key, "::") !== false) {
        list ($name) = explode("::", $key);
        if (isset($names[$name])) continue;
        $names[$name] = true;
      }
    }
    
    return array_keys($names);
  }
  
  public function resetErrors($storage)
  {
    $storage->delete(self::ERROR_KEY);
    $storage->delete(self::STACK_KEY);
  }
}
