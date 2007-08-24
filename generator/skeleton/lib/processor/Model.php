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
    $storage     = $bus->get("storage");
    $request     = $bus->get("request");
    $controller  = $bus->get("controller");
    $destination = $bus->get("destination");
    
    if ($request->isPost()) {
      $toAction = $this->getAnnotationForValidate($destination, $controller);
      $models = $this->createModels($request->fetchPostValues());
      
      foreach ($models as $mdlName => $model) {
        $mdlName{0} = strtolower($mdlName{0});
        
        if ($toAction !== null) {
          if ($errors = $this->validate($model)) {
            $controller->errors = $errors;
            $this->redirect($bus, $controller, $toAction);
            break;
          }
        }
        
        $controller->setAttribute("{$mdlName}Executer", new Manipulator($model));
      }
    }
    
    $executerList = $bus->getList()->find("executer");
    
    if ($executerList !== null) {
      $executer = $executerList->get();
      $bus->callback($executer, $executer->execute($bus));
      $executerList->unlink();
    }

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
      $storage  = $bus->get("storage");
      $messages = $bus->get("controller")->errors;
      if ($messages === null) return;

      $stack  = $storage->read(self::STACK_KEY);
      $index  = count($stack) - 1;
      $values = $bus->get("request")->fetchPostValues();

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
  
  protected function getAnnotationForValidate($destination, $controller)
  {
    $action     = $destination->getAction();
    $annotation = new Sabel_Annotation_ReflectionClass(get_class($controller));
    $methods    = $annotation->getMethodsAsAssoc();
    $elements   = $methods[$action]->getAnnotation("validate");
    
    return ($elements === null) ? null : $elements[1];
  }
  
  protected function validate($model)
  {
    $validator = new Sabel_DB_Validator($model);
    return $validator->validate(array());
  }
  
  protected function redirect($bus, $controller, $toAction)
  {
    $controller->getAttribute("redirect")->to($toAction);
    $redirecter = $bus->getList()->find("redirecter")->get();
    $bus->set("response", $controller->getResponse());
    $redirecter->onRedirect($bus);
    $bus->getList()->find("executer")->unlink();
    
    $this->event($bus, $redirecter, "onRedirect", true);
  }
  
  public function resetErrors()
  {
    $storage = Sabel_Context::getBus()->get("storage");
    $storage->delete(self::ERROR_KEY);
    $storage->delete(self::STACK_KEY);
  }
}
