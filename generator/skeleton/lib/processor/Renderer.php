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
    $redirector = $this->controller->getAttribute("redirect");
    if ($redirector->isRedirected()) return;
    
    $responses = $this->response->getResponses();
    $renderer  = new Sabel_View_Renderer_Class();
    
    if (($resource = $this->repository->find())) {
      $contents = $renderer->rendering($resource->fetch(), $responses);
    } elseif ($this->response->isSuccess()) {
      return true;
    } else {
      $resource = $this->repository->find("notFound");
      if (is_object($resource)) {
        $contents = $renderer->rendering($resource->fetch(), $responses);
      } else {
        $resource = $this->repository->find("serverError");
        $contents = $renderer->rendering($resource->fetch(), $responses);
      }
    }
    
    $layoutName = $this->controller->getAttribute("layout");
    if ($layoutName === null) $layoutName = DEFAULT_LAYOUT_NAME;
    
    if (isset($_SERVER["HTTP_X_REQUESTED_WITH"])) {
      $bus->set("result", $contents);
    } elseif (isset($contents)) {
      $layout = $this->repository->find($layoutName);
      $responses["contentForLayout"] = $contents;
      $this->result = $renderer->rendering($layout->fetch(), $responses);
    }
  }
  
  public function shutdown($bus)
  {
    $this->response->outputHeader();
  }
}
