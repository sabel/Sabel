<?php

class TestProcessor_View extends Sabel_Bus_Processor
{
  protected $beforeEvents = array("initializer" => "initViewObject");
  
  private $view     = null;
  private $renderer = null;
  
  public function execute($bus)
  {
    $controller = $bus->get("controller");
    if ($controller->isRedirected()) return;
    
    if (($this->renderer = $bus->get("renderer")) === null) {
      $this->renderer = new Sabel_View_Renderer();
      $bus->set("renderer", $this->renderer);
    }
    
    $response  = $bus->get("response");
    $responses = $response->getResponses();
    $view = $this->getView($response, $bus->get("destination")->getAction());
    
    if ($controller->renderText) {
      $result = $this->renderer->rendering($controller->contents, $responses);
      return $bus->set("result", $result);
    } elseif ($controller->renderImage) {
      return $bus->set("result", $controller->contents);
    }
    
    if ($template = $view->getValidLocation()) {
      $contents = $this->rendering($template, $responses);
    } elseif ($controller->isExecuted()) {
      $contents = $controller->contents;
      if ($contents === null) $contents = "";
    } else {
      if ($template = $view->getValidLocation("notFound")) {
        $contents = $this->rendering($template, $responses);
      } else {
        $contents = "<h1>404 Not Found</h1>";
        if (DEVELOPMENT === DEVELOPMENT) {
          $contents .= "setup your notFound.tpl to module directory.";
        }
      }
      
      $response->notFound();
    }
    
    $layout = $controller->getAttribute("layout");
    
    if ($layout === false ||
        $bus->get("request")->getHttpHeader("x-requested-with") === "XMLHttpRequest") {
      $bus->set("result", $contents);
    } else {
      if ($layout === null) $layout = DEFAULT_LAYOUT_NAME;
      if ($template = $view->getValidLocation($layout)) {
        $responses["contentForLayout"] = $contents;
        $bus->set("result", $this->rendering($template, $responses));
      } else {
        $bus->set("result", $contents);
      }
    }
  }
  
  public function initViewObject($bus)
  {
    list ($m, $c, $a) = $bus->get("destination")->toArray();
    
    $controller = new Sabel_View_Location_File($m . DS . VIEW_DIR_NAME . DS . $c . DS);
    $view = new Sabel_View_Object("controller", $controller);
    
    $module = new Sabel_View_Location_File($m . DS . VIEW_DIR_NAME . DS);
    $view->addLocation("module", $module);
    
    $app = new Sabel_View_Location_File(VIEW_DIR_NAME . DS);
    $view->addLocation("app", $app);
    
    $this->view = $view;
    
    $bus->set("view", $view);
    $bus->get("controller")->setAttribute("view", $view);
  }
  
  protected function rendering($template, $responses)
  {
    return $this->renderer->rendering($template->getContents(),
                                      $responses,
                                      $template->getPath());
  }
  
  protected function getView($response, $action)
  {
    if ($response->isNotFound()) {
      $this->view->setName("notFound");
    } elseif ($response->isForbidden()) {
      $this->view->setName("forbidden");
    } elseif ($response->isServerError()) {
      $this->view->setName("serverError");
    } elseif ($this->view->getName() === "") {
      $this->view->setName($action);
    }
    
    return $this->view;
  }
}
