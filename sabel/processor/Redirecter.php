<?php

/**
 * Sabel_Processor_Redirecter
 *
 * @category   Processor
 * @package    org.sabel.processor
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Processor_Redirecter extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $controller = $bus->get("controller");
    
    $redirect = new Redirect($bus);
    $controller->setAttribute("redirect", $redirect);
    
    return new Sabel_Bus_ProcessorCallback($this, "onRedirect", "executer");
  }
  
  public function onRedirect($bus)
  {
    $controller = $bus->get("controller");
    $redirect = $controller->getAttribute("redirect");
    
    if ($redirect->isRedirected()) {
      if (isset($_SERVER["HTTP_HOST"])) {
        $host = $_SERVER["HTTP_HOST"];
      } else {
        $host = "localhost";
      }
      
      $ignored = "";
      
      if (defined("URI_IGNORE")) {
        $ignored = ltrim($_SERVER["SCRIPT_NAME"], "/") . "/";
      }
      
      $to = $redirect->getUrl();
      $bus->get("response")->location($host, $ignored . $to);
      
      return true;
    }
    
    return false; 
  }
}

class Redirect
{
  private $bus = null;
  private $url = "";
  private $redirected = false;
  
  const REDIRECTED = "SABEL_REDIRECTED";
  
  public function __construct($bus)
  {
    $this->bus = $bus;
  }
  
  public function isRedirected()
  {
    return $this->redirected;
  }
  
  public function to($destinationUri, $parameters = null)
  {
    $this->redirected = true;
    return $this->redirectTo($destinationUri, $parameters);
  }
  
  public function url($url)
  {
    $this->url = $url;
    $this->redirected = true;
  }
  
  public function getUrl()
  {
    return $this->url;
  }
  
  /**
   * HTTP Redirect to another location.
   *
   * @access public
   * @param string $to /Module/Controller/Method
   * @return mixed self::REDIRECTED
   */
  private function _redirect($to, $parameters = null)
  {
    if ($parameters !== null) {
      $buf = array();
      foreach ($parameters as $key => $value) {
        $buf[] = "{$key}={$value}";
      }
      $to .= "?" . join("&", $buf);
    }
    
    $this->url = $to;
    return self::REDIRECTED;
  }
  
  /**
   * HTTP Redirect to another location with uri.
   *
   * @param string $params
   */
  private function redirectTo($destination, $parameters = null)
  {
    $context = Sabel_Context::getContext();
    $candidate = $context->getCandidate();
    $uri = $candidate->uri($this->convertParams($destination));
    
    return $this->_redirect($uri, $parameters);
  }
  
  private function convertParams($param)
  {
    $buf = array();
    $params = explode(",", $param);
    $reserved = ";";
    
    foreach ($params as $part) {
      $line     = array_map("trim", explode(":", $part));
      $reserved = ($line[0] === "n") ? "candidate" : $line[0];
      $buf[$reserved] = $line[1];
    }
    
    return $buf;
  }
}
