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
    
    $response->outputHeader();
    
    $responses = $response->getResponses();
    
    if (mb_detect_encoding(urldecode($destination->getAction())) === "UTF-8") {
      $destination->setAction(urldecode($destination->getAction()));
    }
    
    $repository = new Sabel_View_Repository_File($destination);
    $renderer   = new Sabel_View_Renderer_Class();
    
    if (($resource = $repository->find())) {
      $contents = $renderer->rendering($resource->fetch(), $responses);
    } else {
      $resource = $repository->getResourceFromLocation("module", "notFound");
      $contents = $renderer->rendering($resource->fetch(), $responses);
    }
    
    if (!isset($_SERVER["HTTP_X_REQUESTED_WITH"])) {
      $repository = new Sabel_View_Repository_File($destination);
      $layout = $repository->find("layout");
      
      $forLayout = array($responses, "contentForLayout" => $contents);
      $result = $renderer->rendering($layout->fetch(), $forLayout);
    } else {
      $result = $contents;
    }
    
    $bus->set("result", $result);

    return true;
  }
}
