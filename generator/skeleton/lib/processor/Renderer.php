<?php

/**
 * Processor_Renderer
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Renderer extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $request     = $bus->get("request");
    $response    = $bus->get("response");
    $destination = $bus->get("destination");
    $repository  = $bus->get("repository");
    
    $response->outputHeader();
    
    $responses = $response->getResponses();
    
    if (mb_detect_encoding(urldecode($destination->getAction())) === "UTF-8") {
      $destination->setAction(urldecode($destination->getAction()));
    }
    
    $renderer = new Sabel_View_Renderer_Class();
    
    if (($resource = $repository->find())) {
      $contents = $renderer->rendering($resource->fetch(), $responses);
    } elseif (!$response->isSuccess()) {
      $resource = $repository->getResourceFromLocation("module", "notFound");
      $contents = $renderer->rendering($resource->fetch(), $responses);
    }
    
    if (isset($_SERVER["HTTP_X_REQUESTED_WITH"])) {
      $bus->set("result", $contents);
    } elseif (isset($contents)) {
      $layout = $repository->find("layout");
      $forLayout = array($responses, "contentForLayout" => $contents);
      $result = $renderer->rendering($layout->fetch(), $forLayout);
      $bus->set("result", $result);
    }
    
    return true;
  }
}
