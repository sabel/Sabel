<?php

/**
 * Internal Request
 *
 * @category   Request
 * @package    org.sabel.request
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Request_Internal extends Sabel_Object
{
  protected
    $bus    = null,
    $method = Sabel_Request::GET,
    $values = array();
    
  protected
    $response = array();
    
  public function __construct($method = Sabel_Request::GET)
  {
    $this->method = $method;
  }
  
  public function values(array $values)
  {
    $this->values = $values;
    
    return $this;
  }
  
  public function method($method)
  {
    $this->method = $method;
    
    return $this;
  }
  
  public function request($uri, Sabel_Bus_Config $config = null)
  {
    $values = $this->values;
    
    $uri = "http://localhost/{$uri}";
    $parsedUri = parse_url($uri);
    
    if (isset($parsedUri["query"])) {
      foreach (explode("&", $parsedUri["query"]) as $param) {
        list ($k, $v) = explode("=", $param);
        $values[$k] = $v;
      }
    }
    
    $currentContext = Sabel_Context::getContext();
    $currentBus = $currentContext->getBus();
    
    $request = new Sabel_Request_Object(ltrim($parsedUri["path"], "/"));
    $request->method($this->method);
    $request->values($values);
    
    $bus = new Sabel_Bus();
    $bus->set("request", $request);
    $bus->set("storage", $currentBus->get("storage"));
    
    $context = new Sabel_Context();
    $context->setBus($bus);
    Sabel_Context::setContext($context);
    
    if ($config === null) {
      $config = new Config_Bus();
    }
    
    $bus->run($config);
    
    $this->response["response"] = $bus->get("response");
    $this->response["result"]   = $bus->get("result");
    
    $currentContext->setBus($currentBus);
    Sabel_Context::setContext($currentContext);
    
    return $this;
  }
  
  public function getResponse()
  {
    if (isset($this->response["response"])) {
      return $this->response["response"];
    } else {
      return null;
    }
  }
  
  public function getResult()
  {
    if (isset($this->response["result"])) {
      return $this->response["result"];
    } else {
      return null;
    }
  }
}
