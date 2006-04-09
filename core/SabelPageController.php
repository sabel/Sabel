<?php

interface DirectoryList
{
  public function listing($path);
}

class DirectoryListImpl implements DirectoryList
{
  public function listing($path)
  {
    $result = array();
    foreach (new DirectoryIterator($path) as $k => $v) {
      if (!$v->isDot() && $v->isFile()) {
        $result[] = $v->getFilename();
      }
    }
    
    return $result;
  }
}

class ClassNameList implements DirectoryList
{
  protected $list = null;
  
  public function __construct(DirectoryList $list)
  {
    $this->list = $list;
  }
  
  public function listing($path)
  {
    $result = array();
    $l = $this->list->listing($path);
    foreach ($l as $k => $file) {
      $classname = explode('.', $file);
      if ($classname[0]) {
        $result[] = $classname[0];
      }
    }
    return $result;
  }
}

class Parameters
{
  protected $parameter;
  protected $parameters;
  protected $parsedParameters;

  public function __construct($parameters)
  {
    $this->parameters = $parameters;
    $this->parse();
  }

  /**
   * Parsing URL request
   *
   * @param void
   * @return void
   */
  protected function parse()
  {
    $parameters = split("\?", $this->parameters);
    $this->parameter = (empty($parameters[0])) ? null : $parameters[0];
    $separate = split("&", $parameters[1]);
    $sets = array();
    foreach ($separate as $key => $val) {
      $tmp = split("=", $val);
      $sets[$tmp[0]] = $tmp[1];
    }
    $this->parsedParameters =& $sets;
  }

  public function getParameter()
  {
    return $this->parameter;
  }

  public function get($key)
  {
    return $this->parsedParameters[$key];
  }
}

interface Response
{
}

class WebResponse implements Response
{
  protected $responses = array();

  public function __set($name, $value)
  {
    $this->responses[$name] = $value;
  }

  public function __get($name)
  {
    return $this->responses[$name];
  }

  public function responses()
  {
    return $this->responses;
  }
}

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
    $class = $r->getModule() . '_' . $r->getController();
    $refMethod = new ReflectionMethod($class, $methodName);
    
    
    foreach ($refMethod->getParameters() as $k => $param) {
      try {
        $classname = $param->getClass()->getName();
      } catch (Exception $e) {
        $msgs = explode(' ', $e->getMessage());
        $classname = $msgs[1];
        $classpath = 'app/commons/models/' . $classname . '.php';
        require_once($classpath);
      }
    }
    
    if ($classname) {
      $requiredClass = new $classname();
      $this->$methodName($requiredClass);
    } else {
      $this->$methodName();
    }
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
