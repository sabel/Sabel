<?php

class Processor_Redirecter_Redirect
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
