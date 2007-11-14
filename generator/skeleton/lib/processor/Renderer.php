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
    
    if ($this->controller->hasAttribute("renderText")) {
      $this->result = $renderer->rendering($this->controller->contents, $responses);
      return;
    } elseif ($this->controller->hasAttribute("renderImage")) {
      $this->result = $this->controller->contents;
      return;
    }
    
    if (($resource = $this->repository->find())) {
      $contents = $renderer->rendering($resource->fetch(), $responses);
    } else {
      $resource = $this->repository->find("notFound");
      if (is_object($resource)) {
        $contents = $renderer->rendering($resource->fetch(), $responses);
      } else {
        $msg = "<h1>404 Not Found</h1>";
        $msg .= "setup your notFound.tpl to module directory";
        $contents = $msg;
      }
    }
    
    $layoutName = $this->controller->getAttribute("layout");
    if ($layoutName === "none") {
      $this->result = $contents;
      return;
    }
    
    $layoutName = $this->controller->getAttribute("layout");
    if ($layoutName === null) $layoutName = DEFAULT_LAYOUT_NAME;
    
    if (isset($_SERVER["HTTP_X_REQUESTED_WITH"])) {
      $this->result = $contents;
    } elseif (isset($contents)) {
      $layout = $this->repository->find($layoutName);
      if (is_object($layout)) {
        $responses["contentForLayout"] = $contents;
        $this->result = $renderer->rendering($layout->fetch(), $responses);
      } else {
        $this->result = $contents;
      }
    }
  }
  
  public function shutdown($bus)
  {
    $this->response->outputHeader();
  }
}
