<?php

/**
 * Internal Request
 *
 * @category   Request
 * @package    org.sabel.request
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Request_Internal extends Sabel_Object
{
  protected
    $bus    = null,
    $method = Sabel_Request::GET,
    $values = array();
    
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
  
  public function request($uri)
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
    
    $request = new Sabel_Request_Object(ltrim($parsedUri["path"], "/"));
    $request->method($this->method);
    $request->values($values);
    
    $config = new Config_Bus();
    $bus = $config->configure()->getBus();
    $bus->set("request", $request);
    $bus->set("storage", $currentContext->getBus()->get("storage"));
    
    $context = new Sabel_Context();
    $context->setBus($bus);
    Sabel_Context::setContext($context);
    
    $bus->run();
    $this->bus = $bus;
    
    Sabel_Context::setContext($currentContext);
    
    return $this;
  }
  
  public function getResponse()
  {
    if (is_object($this->bus)) {
      return $this->bus->get("response");
    } else {
      return null;
    }
  }
  
  public function getHtml()
  {
    if (is_object($this->bus)) {
      return $this->bus->get("result");
    } else {
      return "";
    }
  }
}
