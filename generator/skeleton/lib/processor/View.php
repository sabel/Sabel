<?php

/**
 * Processor_View
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_View extends Sabel_Bus_Processor
{
  protected $beforeEvents = array("initializer" => "initViewObject");
  
  /**
   * @var Sabel_View
   */
  private $view = null;
  
  public function execute($bus)
  {
    $controller = $bus->get("controller");
    if ($controller->isRedirected()) return;
    
    $response  = $bus->get("response");
    $responses = $response->getResponses();
    $view = $this->getView($response, $bus->get("destination")->getAction());
    
    if ($controller->renderText) {
      $renderer = $view->getRenderer();
      return $bus->set("result", $renderer->rendering($controller->contents, $responses));
    } elseif ($controller->renderImage) {
      return $bus->set("result", $controller->contents);
    }
    
    if ($location = $view->getValidLocation()) {
      $contents = $view->rendering($location, $responses);
    } elseif ($controller->isExecuted()) {
      $contents = $controller->contents;
      if ($contents === null) $contents = "";
    } else {
      $response->notFound();
      if ($location = $view->getValidLocation("notFound")) {
        $contents = $view->rendering($location, $responses);
      } else {
        $contents = "<h1>404 Not Found</h1>";
      }
    }
    
    $layout = $controller->getAttribute("layout");
    
    if ($layout === false ||
        $bus->get("request")->getHttpHeader("X-Requested-With") === "XMLHttpRequest") {
      $bus->set("result", $contents);
    } else {
      if ($layout === null) $layout = DEFAULT_LAYOUT_NAME;
      if ($location = $view->getValidLocation($layout)) {
        $responses["contentForLayout"] = $contents;
        $bus->set("result", $view->rendering($location, $responses));
      } else {
        // no layout.
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
    
    if ($renderer = $bus->get("renderer")) {
      $view->setRenderer($renderer);
    } else {
      $view->setRenderer(new Sabel_View_Renderer());
    }
    
    $this->view = $view;
    
    $bus->set("view", $view);
    $bus->get("controller")->setAttribute("view", $view);
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
