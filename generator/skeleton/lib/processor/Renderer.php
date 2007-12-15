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
    $controller = $this->controller;
    $redirector = $controller->getAttribute("redirect");
    if ($redirector->isRedirected()) return;
    
    $responses = $this->response->getResponses();
    $renderer  = new Sabel_View_Renderer_Class();
    
    if ($controller->hasAttribute("renderText")) {
      $this->result = $renderer->rendering($controller->contents, $responses);
      return;
    } elseif ($controller->hasAttribute("renderImage")) {
      $this->result = $controller->contents;
      return;
    }
    
    if ($this->response->isNotFound()) {
      $this->destination->setAction("notFound");
    } elseif ($this->response->isServerError()) {
      $this->destination->setAction("serverError");
    }
    
    if (($resource = $this->repository->find())) {
      $contents = $renderer->rendering($resource->fetch(), $responses);
    } elseif ($controller->isExecuted()) {
      $contents = $controller->contents;
      if ($contents === null) $contents = "";
    } else {
      $resource = $this->repository->find("notFound");
      if (is_object($resource)) {
        $contents = $renderer->rendering($resource->fetch(), $responses);
      } else {
        $contents = "<h1>404 Not Found</h1>"
                  . "setup your notFound.tpl to module directory.";
      }
    }
    
    $layoutName = $controller->getAttribute("layout");
    
    if (isset($contents)) {
      if ($layoutName === "none" || isset($_SERVER["HTTP_X_REQUESTED_WITH"])) {
        $this->result = $contents;
      } else {
        if ($layoutName === null) $layoutName = DEFAULT_LAYOUT_NAME;
        $layout = $this->repository->find($layoutName);
        if (is_object($layout)) {
          $responses["contentForLayout"] = $contents;
          $this->result = $renderer->rendering($layout->fetch(), $responses);
        } else {
          $this->result = $contents;
        }
      }
    }
  }
  
  public function shutdown($bus)
  {
    $this->response->outputHeader();
  }
}
