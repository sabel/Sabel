<?php

/**
 * Processor_Action
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Action extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $response   = $bus->get("response");
    $status     = $response->getStatus();
    $controller = $bus->get("controller");
    
    if ($status->isFailure() || $controller->isRedirected()) return;
    
    try {
      $action  = $bus->get("destination")->getAction();
      $request = $bus->get("request");
      
      if ($request->isPostSet("SBL_CLIENT_ID")) {
        if ($request->fetchPostValue("SBL_CLIENT_ID") !== $controller->getSession()->getClientId()) {
          return $status->setCode(Sabel_Response::BAD_REQUEST);
        }
      }
      
      $controller->setAction($action);
      $controller->initialize();
      
      if ($status->isFailure() || $controller->isRedirected() || !$controller->hasMethod($action)) return;
      
      $reader = Sabel_Annotation_Reader::create();
      $annotations = $reader->readMethodAnnotation($controller, $action);
      
      if (isset($annotations["httpMethod"])) {
        $allows = $annotations["httpMethod"][0];
        if (!$this->isMethodAllowed($request, $allows)) {
          $response->setHeader("Allow", implode(",", array_map("strtoupper", $allows)));
          return $status->setCode(Sabel_Response::METHOD_NOT_ALLOWED);
        }
      }
      
      if (isset($annotations["check"])) {
        $this->validateRequests($controller, $request, $status, $annotations["check"]);
      }
      
      l("execute action '{$action}'");
      $controller->execute();
    } catch (Exception $e) {
      $status->setCode(Sabel_Response::INTERNAL_SERVER_ERROR);
      Sabel_Context::getContext()->setException($e);
    }
  }
  
  protected function isMethodAllowed($request, $allows)
  {
    $result = true;
    foreach ($allows as $method) {
      if (!($result = $request->{"is" . $method}())) break;
    }
    
    return $result;
  }
  
  protected function validateRequests($controller, $request, $status, $checks)
  {
    $validator = new Validator();
    
    foreach ($checks as $check) {
      $name = array_shift($check);
      $validator->set($name, $check);
    }
    
    $method = "fetch" . ucfirst(strtolower($request->getMethod())) . "Values";
    $validator->validate($request->$method());
    $controller->setAttribute("validator", $validator);
    
    if ($validator->hasError()) {
      if ($request->isPost()) {
        $controller->setAttribute("errors", $validator->getErrors());
      } else {
        $status->setCode(Sabel_Response::BAD_REQUEST);
      }
    }
  }
}
