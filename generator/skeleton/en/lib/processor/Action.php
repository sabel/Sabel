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
    
    $action    = $bus->get("destination")->getAction();
    $hasAction = $controller->hasMethod($action);
    $request   = $bus->get("request");
    
    try {
      if ($hasAction) {
        $reader = Sabel_Annotation_Reader::create();
        $annots = $reader->readMethodAnnotation($controller, $action);
        
        if (isset($annots["checkClientId"])) {
          if ($request->fetchPostValue("SBL_CLIENT_ID") !== $controller->getSession()->getClientId()) {
            return $status->setCode(Sabel_Response::BAD_REQUEST);
          }
        }
        
        if (isset($annots["httpMethod"])) {
          $allows = $annots["httpMethod"][0];
          if (!$this->isMethodAllowed($request, $allows)) {
            $response->setHeader("Allow", implode(",", array_map("strtoupper", $allows)));
            return $status->setCode(Sabel_Response::METHOD_NOT_ALLOWED);
          }
        }
      }
      
      $controller->setAction($action);
      $controller->initialize();
      
      if ($status->isFailure() || $controller->isRedirected()) return;
      
      if ($hasAction) {
        l("execute action '{$action}'");
        $controller->execute();
      }
    } catch (Exception $e) {
      $status->setCode(Sabel_Response::INTERNAL_SERVER_ERROR);
      Sabel_Context::getContext()->setException($e);
    }
    
    if ($controller->getAttribute("layout") === false) {
      $bus->set("noLayout", true);
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
}
