<?php

/**
 * ExtController_Processor
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class ExtController_Processor extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $status = $bus->get("response")->getStatus();
    if ($status->isFailure()) return;
    
    $controller = $bus->get("controller");
    $controller->mixin(new ExtController_Mixin($controller));
    
    $request = $bus->get("request");
    $gets    = $request->fetchGetValues();
    $posts   = $request->fetchPostValues();
    $params  = $request->fetchParameterValues();
    $vCount  = count($gets) + count($posts) + count($params);
    $values  = array_merge($gets, $posts, $params);
    
    if (count($values) !== $vCount) {
      l("[ExtController] request key overlaps", SBL_LOG_DEBUG);
      return $status->setCode(Sabel_Response::BAD_REQUEST);
    } else {
      foreach ($values as $name => $value) {
        $controller->setAttribute($name, $value);
      }
      
      $controller->setAttribute("REQUEST_VARS", $values);
      $controller->setAttribute("GET_VARS",     $gets);
      $controller->setAttribute("POST_VARS",    $posts);
    }
    
    $action = $bus->get("destination")->getAction();
    
    if ($controller->hasMethod($action)) {
      $reader = Sabel_Annotation_Reader::create();
      $annots = $reader->readMethodAnnotation($controller, $action);
      if (isset($annots["check"]) && ($request->isGet() || $request->isPost())) {
        if (!$result = $this->validate($controller, $values, $request, $annots["check"])) {
          return $status->setCode(Sabel_Response::BAD_REQUEST);
        }
      }
    }
  }
  
  protected function validate($controller, $values, $request, $checks)
  {
    $validator = new Validator();
    $validator->validate($values);
    
    foreach ($checks as $check) {
      $name = array_shift($check);
      $validator->set($name, $check);
    }
    
    $controller->setAttribute("validator", $validator);
    
    $result = true;
    if (!$validator->validate($values)) {
      if ($request->isPost()) {
        $controller->setAttribute("errors", $validator->getErrors());
      } else {
        $result = false;
      }
    }
    
    return $result;
  }
}
