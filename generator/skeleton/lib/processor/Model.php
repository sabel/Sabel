<?php

/**
 * Processor_Model
 *
 * @category   Processor
 * @package    processor
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Model extends Sabel_Bus_Processor
{
  const MAX_STACK_SIZE = 5;
  const ERROR_KEY      = "errors";
  const STACK_KEY      = "stack";
  
  public function execute($bus)
  {
    $storage    = $bus->get("storage");
    $request    = $bus->get("request");
    $controller = $bus->get("controller");
    
    if ($request->isPost()) {
      $models = $this->createModels($request->fetchPostValues());
      foreach ($models as $mdlName => $model) {
        $controller->setAttribute($mdlName, $model);
      }
    }
    
    $executerList = $bus->getList()->find("executer");
    $executer     = $executerList->get();
    
    $bus->callback($executer, $executer->execute($bus));
    $executerList->unlink();

    $current = $request->getUri()->__toString();
    $errors  = $storage->read(self::ERROR_KEY);
    $ignore  = ($request->isTypeOf("css") || $request->isTypeOf("js"));
    
    if (is_array($errors)) {
      if ($current === $errors["submitUri"]) {
        $controller->setAttribute(self::ERROR_KEY, $errors["messages"]);
        $controller->setAttributes($errors["values"]);
        $models = $this->createModels($errors["values"]);
        
        foreach ($models as $mdlName => $model) {
          $mdlName{0} = strtolower($mdlName{0});
          $controller->setAttribute("{$mdlName}Form", new Helpers_Form($model));
        }
      } elseif (!$ignore && $request->isGet()) {
        $storage->delete(self::ERROR_KEY);
      }
    }
    
    if (!$ignore) {
      $stack = $storage->read(self::STACK_KEY);
      
      if (is_array($stack)) {
        $stack[] = $current;
        if (count($stack) > self::MAX_STACK_SIZE) array_shift($stack);
      } else {
        $stack = array($current);
      }
      
      $storage->write(self::STACK_KEY, $stack);
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
  
  protected function createModels($values)
  {
    $models = array();
    foreach ($values as $key => $value) {
      if (strpos($key, "::") !== false) {
        list ($mdlName, $colName) = explode("::", $key);
        if (!isset($models[$mdlName])) {
          $models[$mdlName] = MODEL($mdlName);
        }
        
        $models[$mdlName]->$colName = $value;
      }
    }
    
    return $models;
  }
  
  public function resetErrors()
  {
    $storage = Sabel_Context::getBus()->get("storage");
    $storage->delete(self::ERROR_KEY);
    $storage->delete(self::STACK_KEY);
  }
}
