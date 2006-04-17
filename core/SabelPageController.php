<?php

require_once('Parameters.php');
require_once('Response.php');
require_once('DirectoryList.php');

/**
 * page controller base class.
 *
 * @author Mori Reo <mori.reo@servise.jp>
 * @package sabel.controller
 */
abstract class SabelPageController
{
  protected $request, $template, $cache;

  /**
   * implement for inherit class.
   *
   */
  abstract function initialize();
  abstract function index();

  /**
   * get request parameter
   *
   * @param string input name
   * @return mixed
   */
  public function __get($name)
  {
    $safe = substr($name, 0, 4);

    if ($safe == 'safe') {
      $lower = strtolower(substr($name, 4, (strlen($name) - 4)));
      return addslashes($this->request->$lower);
    }

    return $this->request->$name;
  }

  public function setup($request)
  {
    $this->request = $request;
    $this->setupResponse();
    $this->setTemplate(new HtmlTemplate());
    $this->setupConfig();
    $this->setupCache();
  }

  protected function setupResponse()
  {
    $this->response = new WebResponse();
  }

  protected function setupConfig()
  {
    $this->config = CachedConfigImpl::create();
  }

  protected function setupCache()
  {
    $conf = $this->config->get('Memcache');
    $this->cache = MemCacheImpl::create($conf['server']);
  }

  public function execute($method)
  {
    $this->checkValidateMethodAndExecute($method);
    $this->methodExecute($method);
    $this->initTemplate();
    $this->showTemplate();
  }
  
  protected function methodExecute($methodName)
  {
    $r = ParsedRequest::create();
    $controllerClass = $r->getModule() . '_' . $r->getController();
    $refMethod = new ReflectionMethod($controllerClass, $methodName);
    
    $hasParameter = false;
    foreach ($refMethod->getParameters() as $paramidx => $parameter) {
      $hasParameter = true;
      $hasDependClass = ($refClass = $parameter->getClass()) ? true : false;
      if ($hasDependClass) {
        $structure = array();
        
        SabelDIContainer::parseClassDependencyStructure($refClass->getName(), $structure);
        
        $typeIsInterface = ($structure[$refClass->getName()]['type'] == 'interface') ? true : false;
        if ($typeIsInterface) {
          $diconf = $this->loadDIConfig($refClass->getName());
          $implClassName = $diconf[$controllerClass][$methodName][$refClass->getName()];
          //var_dump($diconf);exit;
          SabelDIContainer::parseClassDependencyStructure($implClassName, $structure);
          
          foreach ($structure[$implClassName]['__construct'] as $implparamidx => $implParameter) {
            if ($implParameter['define']['type'] == 'interface') {
              $dependClassPath = $diconf[$controllerClass][$methodName][$implClassName]['constructer'];
              $dependClassName = uses($dependClassPath);
              $dependClass = new $dependClassName();
            }
          }
        } // else if (has't depend class.) {}
      }
    }
    
    if ($hasParameter) {
      $this->$methodName(new $implClassName($dependClass));
    } else {
      $this->$methodName();
    }
    
  }
  
  protected function loadDIConfig()
  {
    $r = ParsedRequest::create();
    $controller = strtolower($r->getController());
    $paths = array('app/modules/staff/controllers/', 'app/modules/staff/controllers/');
    $spyc = new Spyc();
    foreach ($paths as $pathidx => $path) {      
      $fullpath = $path . $controller. '.yml';
      if (is_file($fullpath)) break;
    }

    return $spyc->load($fullpath);
  }
  
  protected function assignTemplates()
  {
    foreach ($this->response->responses() as $key => $val)
      $this->template->assign($key, $val);
  }

  protected function checkValidateMethodAndExecute($method)
  {
    if ($this->hasValidateMethod($method)) {
      return $this->executeValidateMethod($method);
    } else {
      return true;
    }
  }

  protected function executeValidateMethod($method)
  {
    $validateMethod = 'validate' . ucfirst($method);
    return $this->$validateMethod();
  }

  public function hasMethod($name)
  {
    if (method_exists($this, $name)) {
      return true;
    } else {
      return false;
    }
  }

  protected function setTemplate($template)
  {
    $this->template = $template;
  }

  protected function getActionMethods()
  {
    $methods = get_class_methods($this);

    $ar = array();
    foreach ($methods as $key => $val) {
      if ($val[0] != '_') {
        $ar[$key] = $val;
      }
    }
    return $ar;
  }

  protected function hasValidateMethod($methodName)
  {
    $methods = $this->getActionMethods();
    $vMethodName =(string) 'validate'. ucfirst($methodName);

    $found = false;
    foreach ($methods as $k => $method) {
      if ($method === $vMethodName) $found = true;
    }

    return $found;
  }

  protected function checkReferer($validURIs)
  {
    $ref = $_SERVER['HTTP_REFERER'];
    $replaced = preg_replace('/\\//', '\/', $validURIs[0]);
    $patternAbsoluteURI = '/http:\/\/' . $_SERVER['HTTP_HOST'] . $replaced . '/';
    preg_match($patternAbsoluteURI, $ref, $matchs);
    return (isset($matchs[0])) ? true : false;
  }

  /**
   * HTTP Redirect to another location.
   * this method will be avoid "back button" problem.
   *
   * @param string $to /Module/Controller/Method
   */
  protected function redirect($to)
  {
    $absolute  = 'http://' . $_SERVER['HTTP_HOST'];
    $redirect  = 'Location: ' . $absolute . $to;
    header($redirect);

    exit; // exit after HTTP Header(30x)
  }

  /**
   * forwaring anothor controller or method of same controller.
   *
   */
  protected function forward($to)
  {
    // @todo implemen
  }

  /**
   * initialize template
   */
  protected function initTemplate()
  {
    $d = TemplateDirectorFactory::create();
    $this->template->selectPath($d->decidePath());
    $this->template->selectName($d->decideName());
  }

  /**
   * process template then rendering it.
   *
   */
  protected function showTemplate()
  {
    try {
      $this->template->rendering();
    } catch(SabelException $e) {
      $e->printStackTrace();
    }
  }
}

?>
