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
    $controller = $bus->get("controller");
    $redirector = $controller->getAttribute("redirect");
    
    if (is_object($redirector) && $redirector->isRedirected()) return;
    
    $responses = $bus->get("response")->getResponses();
    $renderer = $bus->get("renderer");
    
    if (!is_object($renderer)) {
      $renderer = new Sabel_View_Renderer();
      $bus->set("renderer", $renderer);
    }
    
    if ($controller->renderText) {
      $this->result = $this->rendering($renderer, $controller->contents, $responses);
      return;
    } elseif ($controller->renderImage) {
      $this->result = $controller->contents;
      return;
    }
    
    $repository = $this->getRepository($bus);
    
    if ($template = $repository->getValidTemplate()) {
      $contents = $this->rendering($renderer, $template, $responses);
    } elseif ($controller->isExecuted()) {
      $contents = $controller->contents;
      if ($contents === null) $contents = "";
    } elseif ($template = $repository->getValidTemplate("notFound")) {
      $contents = $this->rendering($renderer, $template, $responses);
    } else {
      $contents = "<h1>404 Not Found</h1>";
      if (DEVELOPMENT === DEVELOPMENT) {
        $contents .= "setup your notFound.tpl to module directory.";
      }
    }
    
    $layoutName = $controller->getAttribute("layout");
    
    if ($layoutName === "none" || isset($_SERVER["HTTP_X_REQUESTED_WITH"])) {
      $this->result = $contents;
    } else {
      if ($layoutName === null) $layoutName = DEFAULT_LAYOUT_NAME;
      if ($template = $repository->getValidTemplate($layoutName)) {
        $responses["contentForLayout"] = $contents;
        $this->result = $this->rendering($renderer, $template, $responses);
      } else {
        $this->result = $contents;
      }
    }
  }
  
  private function rendering(Sabel_View_Renderer $renderer, $template, $responses)
  {
    if (is_object($template)) {
      $contents = $template->getContents();
      $path = $template->getPath();
    } else {
      $contents = $template;
      $path = null;
    }
    
    return $renderer->rendering($contents, $responses, $path);
  }
  
  private function getRepository($bus)
  {
    $response   = $bus->get("response");
    $repository = $bus->get("repository");
    
    if ($response->isNotFound()) {
      $repository->setTemplateName("notFound");
    } elseif ($response->isForbidden()) {
      $repository->setTemplateName("forbidden");
    } elseif ($response->isServerError()) {
      $repository->setTemplateName("serverError");
    } elseif ($repository->getTemplateName() === null) {
      $repository->setTemplateName($bus->get("destination")->getAction());
    }
    
    return $repository;
  }
}
