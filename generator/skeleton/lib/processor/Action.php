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
            return $response->getStatus()->setCode(Sabel_Response::BAD_REQUEST);
          }
        }
        
        if ($request->isPost() && isset($annotations["check"])) {
          $this->validateRequests($controller, $request, $annotations["check"]);
        }
        
        l("execute action '{$action}'");
        $controller->execute();
      }
    } catch (Exception $e) {
      $response->getStatus()->setCode(Sabel_Response::INTERNAL_SERVER_ERROR);
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
  
  protected function validateRequests($controller, $request, $checks)
  {
    $validator = new Validator();
    foreach ($checks as $check) {
      $validator->set($check[0], $check[1]);
    }
    
    $validator->validate($request->fetchPostValues());
    if ($validator->hasError()) {
      $controller->setAttribute("errors", $validator->getErrors());
    }
    
    $controller->setAttribute("validator", $validator);
  }
}
