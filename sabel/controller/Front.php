<?php

/**
 * Sabel_Controller_Front
 *
 * @category   Controller
 * @package    org.sabel.controller
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
final class Sabel_Controller_Front
{
  public
    $plugin = null;
  
  protected
    $injector     = null,
    $filters      = null,
    $controller   = null,
    $destination  = null,
    $request      = null;
    
  public function __construct($request = null)
  {
    if ($this->request === null) {
      $this->injector = Sabel_Container::injector(new Factory());
      $this->request = $this->injector->newInstance("Sabel_Request");
    }
    
    $this->plugin = Sabel_Plugin::create();
  }
    
  public function ignition($storage = null)
  {
    Sabel_Context::log("request " . $this->request);
    Sabel_Context::initialize();
    
    $destination = $this->destination;
    Sabel_Context::setDestination($destination);
    
    Sabel_Helper::load($this->request, $destination);
    
    $this->processPreFilter();
    
    $executer = $this->injector->newInstance("Sabel_Controller_Executer");
    $executer->setDestination($destination);
    $this->controller = $executer->create();
    
    $response = $executer->execute($this->request, $storage);
    
    if ($response->isNotFound() || $response->isServerError()) {
      $response->outputHeader();
      if ($response->isNotFound()) {
        $destination->setAction("notFound");
      } elseif ($response->isServerError()) {
        $destination->setAction("serverError");
      }
      $response = $executer->execute($this->request, $storage);
      if ($response->isNotFound()) {
        $destination->setController("index");
        $this->controller = $executer->create();
        $response = $executer->execute($this->request, $storage);
      }
    }
    
    $this->processPostFilter();
    
    return $response;
  }
  
  public function getController()
  {
    return $this->controller;
  }
  
  public function processCandidate($request = null)
  {
    if ($request !== null) {
      $this->request = $request;
    }
    
    $config = new Map();
    $config->configure();
    
    foreach($config->getRoutes() as $route) {
      $name = $route->getName();
      $uri  = $route->getUri();
      $options = array();
      
      if ($route->hasModule()) {
        $options["module"] = $route->getModule();
      }
      
      if ($route->hasController()) {
        $options["controller"] = $route->getController();
      }
      
      if ($route->hasAction()) {
        $options["action"] = $route->getAction();
      }
      
      $options["default"]     = $route->getDefaults();
      $options["requirement"] = $route->getRequirements();
      
      Sabel_Map_Configurator::addCandidate($name, $uri, $options);
    }
    
    $candidate = new Sabel_Map_Candidate();
    $tokens    = new Sabel_Map_Tokens($this->request->__toString());
    $candidate = $candidate->find($tokens);
    
    Sabel_Context::setCandidate($candidate);
    $this->request->setCandidate($candidate);
    
    $this->destination = $candidate->getDestination();
    
    return $this;
  }
  
  private final function loadFilters()
  {
    if ($this->filters !== null) {
      return $this->filters;
    }
    
    $module = $this->destination->getModule();
    
    $sharedFiltersDir = RUN_BASE . "/app/filters";
    $filtersDir       = RUN_BASE . "/app/{$module}/filters";
    $filters = array();
    
    if (is_dir($sharedFiltersDir)) {
      if ($dh = opendir($sharedFiltersDir)) {
        while (($file = readdir($dh)) !== false) {
          if ($file{0} !== ".") {
            if (is_file($filtersDir . "/{$file}")) {
              // use derived class
              $filters[] = join("_", array(ucfirst($module),
                                           "Filters",
                                            str_replace(".php", "", $file)));
            } elseif (is_file($sharedFiltersDir . "/{$file}")) {
              $class = array('Filters', str_replace(".php", "", $file));
              $filters[] = join("_", $class);
            }
          }
        }
        closedir($dh);
      }
    }
    
    $this->filters = $filters;
    return $filters;
  }
  
  private final function processPreFilter()
  {
    $filters = $this->loadFilters();
    $request = $this->request;
    
    foreach ($filters as $filter) {
      $aFilter = new $filter();
      $aFilter->setup($request)->execute();
      $aFilter->input();
    }
    
    return $filters;
  }
  
  private final function processPostFilter()
  {
    $filters = $this->loadFilters();
    $controller = $this->controller;
    
    foreach ($filters as $filter) {
      $instance = new $filter();
      $instance->output($controller);
    }
  }
  
  public function getResult()
  {
    $controller  = $this->controller;
    $destination = $this->destination;
    
    if ($controller->hasRendered()) {
      return $controller->getRendered();
    }
    
    $view = new Sabel_View();
    $view->assignByArray($controller->getRequests());
    
    $html = "";
    $assigns = array("assign" => array_merge($controller->getAssignments(),
                                             $controller->getAttributes()));
    
    try {
      $content = Sabel_View::render($destination, $assigns);
    } catch (Exception $e) {
      $content = "";
    }
    
    if (isset($_SERVER["HTTP_X_REQUESTED_WITH"])) {
      $html = $content;
    } elseif (!Sabel_Context::isLayoutDisabled()) {
      $assign = array("assign" => array("contentForLayout" => $content));
      try {
        $content = Sabel_View::render($destination, $assigns);
        $d = clone $this->destination;
        $d->setAction(Sabel_Const::DEFAULT_LAYOUT);
        $html = Sabel_View::render($d, $assign);
      } catch (Exception $e) {
        $html = $content;
      }
      if ($html === null) $html = $content;
    } else {
      $html = $content;
    }
    
    return $html;
  }
}
