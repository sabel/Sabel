<?php

class TestProcessor_View extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $controller = $bus->get("controller");
    if ($controller->isRedirected()) return;
    
    $this->prepare($bus);
    
    $responses  = $this->response->getResponses();
    $repository = $this->getRepository($bus->get("destination"));
    
    if ($controller->renderText) {
      $result = $this->renderer->rendering($controller->contents, $responses);
      return $bus->set("result", $result);
    } elseif ($controller->renderImage) {
      return $bus->set("result", $controller->contents);
    }
    
    if ($template = $repository->getValidTemplate()) {
      $contents = $this->rendering($template, $responses);
    } elseif ($controller->isExecuted()) {
      $contents = $controller->contents;
      if ($contents === null) $contents = "";
    } else {
      if ($template = $repository->getValidTemplate("notFound")) {
        $contents = $this->rendering($template, $responses);
      } else {
        $contents = "<h1>404 Not Found</h1>";
        if (DEVELOPMENT === DEVELOPMENT) {
          $contents .= "setup your notFound.tpl to module directory.";
        }
      }
      
      $this->response->notFound();
    }
    
    $layoutName = $controller->getAttribute("layout");
    
    if ($layoutName === "none" || isset($_SERVER["HTTP_X_REQUESTED_WITH"])) {
      $bus->set("result", $contents);
    } else {
      if ($layoutName === null) $layoutName = DEFAULT_LAYOUT_NAME;
      if ($template = $repository->getValidTemplate($layoutName)) {
        $responses["contentForLayout"] = $contents;
        $bus->set("result", $this->rendering($template, $responses));
      } else {
        $bus->set("result", $contents);
      }
    }
  }
  
  private function rendering($template, $responses)
  {
    return $this->renderer->rendering($template->getContents(),
                                      $responses,
                                      $template->getPath());
  }
  
  protected function prepare($bus)
  {
    $this->extract("response", "repository", "renderer");
    
    if ($this->renderer === null) {
      $this->renderer = new Sabel_View_Renderer();
      $bus->set("renderer", $this->renderer);
    }
  }
  
  private function getRepository($destination)
  {
    $response = $this->response;
    
    if ($response->isNotFound()) {
      $this->repository->setTemplateName("notFound");
    } elseif ($response->isForbidden()) {
      $this->repository->setTemplateName("forbidden");
    } elseif ($response->isServerError()) {
      $this->repository->setTemplateName("serverError");
    } elseif ($this->repository->getTemplateName() === null) {
      $this->repository->setTemplateName($destination->getAction());
    }
    
    return $this->repository;
  }
}
