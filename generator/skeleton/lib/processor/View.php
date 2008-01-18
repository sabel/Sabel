<?php

/**
 * Processor_View
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Mori Reo <mori.reo@gmail.com>
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_View extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $controller = $this->controller;
    $redirector = $controller->getAttribute("redirect");
    
    if ($redirector->isRedirected()) return;
    
    if (!is_object($this->renderer)) {
      $this->renderer = new Processor_View_DefaultRenderer();
    }
    
    $responses = $this->response->getResponses();
    
    if ($controller->renderText) {
      $this->result = $this->rendering($controller->contents, $responses);
    } elseif ($controller->renderImage) {
      $this->result = $controller->contents;
    }
    
    if ($bus->has("result")) return;
    
    $repository = $this->getRepository();
    
    if ($resource = $repository->find()) {
      $contents = $this->rendering($resource, $responses);
    } elseif ($controller->isExecuted()) {
      $contents = $controller->contents;
      if ($contents === null) $contents = "";
    } elseif ($resource = $repository->find("notFound")) {
      $contents = $this->rendering($resource, $responses);
    } else {
      $contents = "<h1>404 Not Found</h1>"
                . "setup your notFound.tpl to module directory.";
    }
    
    $layoutName = $controller->getAttribute("layout");
    
    if ($layoutName === "none" || isset($_SERVER["HTTP_X_REQUESTED_WITH"])) {
      $this->result = $contents;
    } else {
      if ($layoutName === null) $layoutName = DEFAULT_LAYOUT_NAME;
      if ($layout = $repository->find($layoutName)) {
        $responses["contentForLayout"] = $contents;
        $this->result = $this->rendering($layout, $responses);
      } else {
        $this->result = $contents;
      }
    }
  }
  
  public function shutdown($bus)
  {
    $this->response->outputHeader();
  }
  
  private function rendering($resource, $responses)
  {
    if (is_object($resource)) {
      $contents = $resource->fetch();
      $path = $resource->getPath();
    } else {
      $contents = $resource;
      $path = null;
    }
    
    return $this->renderer->rendering($contents, $responses, $path);
  }
  
  private function getRepository()
  {
    $repository = $this->repository;
    
    if ($this->response->isNotFound()) {
      $repository->setTemplateName("notFound");
    } elseif ($this->response->isForbidden()) {
      $repository->setTemplateName("forbidden");
    } elseif ($this->response->isServerError()) {
      $repository->setTemplateName("serverError");
    } elseif ($repository->getTemplateName() === null) {
      $repository->setTemplateName($this->destination->getAction());
    }
    
    return $repository;
  }
}
