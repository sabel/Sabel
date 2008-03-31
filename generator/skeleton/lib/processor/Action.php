<?php

/**
 * Processor_Action
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Action extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $response   = $bus->get("response");
    $controller = $bus->get("controller");
    
    if ($response->isFailure() || $controller->isRedirected()) return;
    
    try {
      $action = $bus->get("destination")->getAction();
      $controller->setAction($action);
      $controller->initialize();
      
      if (!$response->isFailure()      &&
          !$controller->isRedirected() &&
          $controller->hasMethod($action)) {
        
        $reader = Sabel_Annotation_Reader::create();
        $annotations = $reader->readMethodAnnotation($controller, $action);
        $request = $bus->get("request");
        
        if (isset($annotations["httpMethod"])) {
          if (!$this->checkRequestMethod($request, $annotations["httpMethod"][0])) {
            return $response->badRequest();
          }
        }
        
        if ($request->isPost() && isset($annotations["check"])) {
          $validator = $this->validateRequests($request, $annotations["check"]);
          $controller->setAttribute("validator", $validator);
        }
        
        l("execute action '{$action}'");
        $controller->execute();
      }
    } catch (Exception $e) {
      $response->serverError();
      Sabel_Context::getContext()->setException($e);
    }
  }
  
  protected function checkRequestMethod($request, $allows)
  {
    $result = true;
    foreach ($allows as $method) {
      if (!($result = $request->{"is" . $method}())) break;
    }
    
    return $result;
  }
  
  protected function validateRequests($request, $checks)
  {
    $validator = new Validator();
    foreach ($checks as $check) {
      $validator->set($check[0], $check[1]);
    }
    
    $validator->validate($request->fetchPostValues());
    return $validator;
  }
}
