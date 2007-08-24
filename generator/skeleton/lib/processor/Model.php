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
  const STACK_SIZE = 5;
  const ERROR_KEY  = "errors";
  const STACK_KEY  = "stack";
  
  const ANNOTATION_VALIDATION_FAILURE = "validation_failure";
  const ANNOTATION_VALIDATION_IGNORES = "validation_ignores";
  
  public function execute($bus)
  {
    $storage     = $bus->get("storage");
    $request     = $bus->get("request");
    $controller  = $bus->get("controller");
    $destination = $bus->get("destination");
    
    if ($request->isPost()) {
      $result = $this->getAnnotationForValidate($destination, $controller);
      $models = $this->createModels($request->fetchPostValues());
      list ($toAction, $ignores) = $result;
      
      $errored = false;
      foreach ($models as $mdlName => $model) {
        $mdlName{0} = strtolower($mdlName{0});
        
        if ($toAction !== null) {
          if ($errors = $this->validate($model, $ignores)) {
            $controller->errors = $errors;
            $this->redirect($bus, $controller, $toAction);
            $errored = true;
            break;
          }
        }
        
        $controller->setAttribute("{$mdlName}Manipulator", new Manipulator($model));
      }
      
      if (!$errored) {
        $this->resetErrors($storage);
      }
    }
    
    if (($executerList = $bus->getList()->find("executer")) !== null) {
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
    
    if (isset($methods[$action])) {
      $method = $methods[$action];
      return array($method->getAnnotation(self::ANNOTATION_VALIDATION_FAILURE),
                   $method->getAnnotation(self::ANNOTATION_VALIDATION_IGNORES));
    } else {
      return array(null, null);
    }
  }
  
  protected function validate($model, $ignores)
  {
    if ($ignores === null) {
      $ignores = array();
    } elseif (is_string($ignores)) {
      $ignores = array($ignores);
    }
    
    $validator = new Sabel_DB_Validator($model);
    return $validator->validate($ignores);
  }
  
  protected function redirect($bus, $controller, $toAction)
  {
    $controller->getAttribute("redirect")->to($toAction);
    $bus->set("response", $controller->getResponse());
    $redirecter = $bus->getList()->find("redirecter")->get();
    $redirecter->onRedirect($bus);
    $bus->getList()->find("executer")->unlink();
    
    $this->event($bus, $redirecter, "onRedirect", true);
  }
  
  public function resetErrors($storage = null)
  {
    if ($storage === null) {
      $storage = Sabel_Context::getContext()->getBus()->get("storage");
    }
    
    $storage->delete(self::ERROR_KEY);
    $storage->delete(self::STACK_KEY);
  }
}
