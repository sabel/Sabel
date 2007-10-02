<?php

class Processor_Redirecter_Redirect
{
  private $bus = null;
  private $url = "";
  private $redirected = false;
  private $parameters = array();
  
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
    $this->parameters = $parameters;
    return $this->redirectTo($destinationUri);
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
  
  public function hasParameters()
  {
    return (count($this->parameters) >= 1);
  }
  
  /**
   * HTTP Redirect to another location.
   *
   * @access public
   * @param string $to /Module/Controller/Method
   * @return mixed self::REDIRECTED
   */
  private function _redirect($to)
  {
    if ($this->hasParameters()){
      $buf = array();
      foreach ($this->parameters as $key => $value) {
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
  private function redirectTo($destination)
  {
    $context = Sabel_Context::getContext();
    $candidate = $context->getCandidate();
    $uri = $candidate->uri($this->convertParams($destination));
    
    return $this->_redirect($uri);
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
