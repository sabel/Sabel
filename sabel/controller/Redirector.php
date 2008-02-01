<?php

/**
 * @category   Controller
 * @package    org.sabel.controller
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2002-2006 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Controller_Redirector
{
  /**
   * @var string
   */
  protected $url = "";
  
  /**
   * @var boolean
   */
  protected $redirected = false;
  
  /**
   * @var array
   */
  protected $parameters = array();
  
  public function isRedirected()
  {
    return $this->redirected;
  }
  
  public function to($destinationUri, $parameters = array())
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
    return (count($this->parameters) > 0);
  }
  
  /**
   * HTTP Redirect to another location.
   *
   * @param string $to /Module/Controller/Method
   *
   * @return void
   */
  private function _redirect($to)
  {
    if ($this->hasParameters()){
      $buffer = array();
      foreach ($this->parameters as $k => $v) $buffer[] = "{$k}={$v}";
      $this->url = $to . "?" . implode("&", $buffer);
    } else {
      $this->url = $to;
    }
  }
  
  /**
   * HTTP Redirect to another location with uri.
   *
   * @param string $destination
   */
  protected function redirectTo($destination)
  {
    $candidate = Sabel_Context::getContext()->getCandidate();
    return $this->_redirect($candidate->uri($this->convertParams($destination)));
  }
  
  private function convertParams($params)
  {
    $buffer   = array();
    $reserved = ";";
    
    foreach (explode(",", $params) as $param) {
      list ($key, $val) = array_map("trim", explode(":", $param));
      if ($key === "n") $key = "candidate";
      $buffer[$key] = $val;
    }
    
    return $buffer;
  }
}
