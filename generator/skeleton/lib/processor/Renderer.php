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
    $controller  = $bus->get("controller");
    $response    = $bus->get("response");
    $destination = $bus->get("destination");
    $repository  = $bus->get("repository");
    
    $response->outputHeader();
    
    $redirector = $controller->getAttribute("redirect");
    if ($redirector->isRedirected()) {
      return true;
    }
    
    $responses = $response->getResponses();
    $renderer  = new Sabel_View_Renderer_Class();
    
    if (($resource = $repository->find())) {
      $contents = $renderer->rendering($resource->fetch(), $responses);
    } elseif ($response->isSuccess()) {
      return true;
    } else {
      $resource = $repository->getResourceFromLocation("module", "notFound");
      if (is_object($resource)) {
        $contents = $renderer->rendering($resource->fetch(), $responses);
      }
    }
    
    $layoutName = $controller->getAttribute("layout");
    if ($layoutName === null) $layoutName = "layout";
    
    if (isset($_SERVER["HTTP_X_REQUESTED_WITH"])) {
      $bus->set("result", $contents);
    } elseif (isset($contents)) {
      $layout = $repository->find($layoutName);
      $responses["contentForLayout"] = $contents;
      $result = $renderer->rendering($layout->fetch(), $responses);
      $bus->set("result", $result);
    }
    
    return true;
  }
}
