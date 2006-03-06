<?php

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

  public function parse()
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

/**
 * �ڡ�������ȥ���δ��쥯�饹
 *
 * @author Mori Reo <mori.reo@servise.jp>
 * @package sabel.controller
 */
abstract class SabelPageController
{
  public
    $parsedRequest;

  protected
    $parameters,
    $postRequest,
    $template,
    $cache;

  /**
   * �Ѿ��襯�饹�Ǽ���
   *
   */
  abstract function initialize();
  abstract function defaults();

  public function setup($parsedRequest)
  {
    $this->setParsedRequest($parsedRequest);
    $this->postRequest = new PostRequest();
    $this->setTemplate(new HtmlTemplate());
    $this->setupConfig();
    $this->setupCache();
    $this->setupParameters();
  }

  protected function setupConfig()
  {
    $this->config = new CachedConfig(new ConfigImpl());
  }

  protected function setupCache()
  {
    $this->cache = new MemCacheImpl();
  }

  protected function setupParameters()
  {
    $this->parameters = new Parameters($this->parsedRequest->getParameter());
  }

  public function execute($method)
  {
    $this->checkValidateMethodAndExecute($method);
    $this->$method();
    $this->initTemplate();
    $this->showTemplate();
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

  protected function setParsedRequest($request)
  {
    $this->parsedRequest = $request;
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

    exit; // HTTP�إå�(30x)������������Ͻ������³���ʤ�
  }

  /**
   * forwaring anothor controller or method of same controller.
   *
   */
  protected function forward($to)
  {
    // @todo implement
  }

  /**
   * �ƥ�ץ졼�Ȥ�����
   */
  protected function initTemplate()
  {
    $d = TemplateDirectorFactory::create($this->parsedRequest);
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