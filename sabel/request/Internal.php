<?php

/**
 * Internal Request
 *
 * @category   Request
 * @package    org.sabel.request
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Request_Internal extends Sabel_Object
{
  /**
   * @var const Sabel_Request
   */
  protected $method = Sabel_Request::GET;
  
  /**
   * @var array
   */
  protected $values = array();
  
  /**
   * @var array
   */
  protected $response = array();
  
  /**
   * @var boolean
   */
  protected $withLayout = false;
  
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
  
  public function withLayout($bool)
  {
    $this->withLayout = $bool;
    
    return $this;
  }
  
  public function request($uri, Sabel_Bus_Config $config = null)
  {
    if (strpos($uri, ":")) {
      $uri = uri($uri);
    }
    
    $uri = "http://localhost/{$uri}";
    $parsedUri = parse_url($uri);
    $request = new Sabel_Request_Object(ltrim($parsedUri["path"], "/"));
    
    if (isset($parsedUri["query"])) {
      parse_str($parsedUri["query"], $get);
      if ($this->method === Sabel_Request::GET) {
        $this->values = array_merge($this->values, $get);
      } else {
        $request->setGetValues($get);
      }
    }
    
    $currentContext = Sabel_Context::getContext();
    $currentBus = $currentContext->getBus();
    
    $request->method($this->method);
    $request->values($this->values);
    
    Sabel_Context::setContext(new Sabel_Context());
    
    $bus = new Sabel_Bus();
    $bus->set("request",   $request);
    $bus->set("session",   $currentBus->get("session"));
    $bus->set("NO_LAYOUT", !$this->withLayout);
    
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
