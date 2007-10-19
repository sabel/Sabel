<?php

/**
 * Processor_Errors
 *
 * @category   Processor
 * @package    lib.processor
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
    $modelForm  = $controller->getAttribute("modelForm");
    $current    = $request->getUri()->__toString();
    $ignore     = ($request->isTypeOf("css") || $request->isTypeOf("js"));
    $errors     = $storage->read(self::ERROR_KEY);
    
    if (is_array($errors)) {
      if ($current === $errors["submitUri"]) {
        $mdlValues = $errors["values"];
        $mdlNames  = $this->getModelNames($mdlValues);
        
        $controller->setAttribute(self::ERROR_KEY, $errors["messages"]);
        $controller->setAttributes($errors["values"]);
        
        if (is_object($modelForm) && !empty($mdlNames)) {
          $models = $this->createModels($modelForm, $mdlNames, $mdlValues);
          foreach ($models as $mdlName => $model) {
            $mdlName{0} = strtolower($mdlName{0});
            $controller->setAttribute("{$mdlName}Form", new Form($model));
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
  
  public function shutdown($bus)
  {
    $storage    = $bus->get("storage");
    $controller = $bus->get("controller");
    $redirect   = $controller->getAttribute("redirect");
    
    if ($redirect->isRedirected()) {
      if (($messages = $controller->errors) === null) {
        $this->resetErrors($storage);
      } else {
        $stack  = $storage->read(self::STACK_KEY);
        $index  = count($stack) - 2;
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
  
  protected function createModels($modelForm, $mdlNames, $mdlValues)
  {
    $models = array();
    foreach ($mdlNames as $name) {
      $models[$name] = $modelForm->createModel($name, null, $mdlValues);
    }
    
    return $models;
  }
  
  public function resetErrors($storage)
  {
    l("********** resetErrors() **********");
    $storage->delete(self::ERROR_KEY);
    $storage->delete(self::STACK_KEY);
  }
}
