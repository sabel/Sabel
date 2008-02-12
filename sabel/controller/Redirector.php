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
  
  /**
   * @return boolean
   */
  public function isRedirected()
  {
    return $this->redirected;
  }
  
  /**
   * HTTP Redirect to another location with uri.
   *
   * @param string $uriParameter
   * @param array  $parameters
   *
   * @return void
   */
  public function to($uriParameter, $parameters = array())
  {
    if (defined("SID") && SID !== "") {
      list ($sesName, $sesId) = explode("=", SID);
      $parameters[$sesName] = $sesId;
    }
    
    $this->redirected = true;
    $this->parameters = $parameters;
    
    $candidate = Sabel_Context::getContext()->getCandidate();
    $this->_redirect($candidate->uri($uriParameter));
  }
  
  /**
   * @param string $url
   *
   * @return void
   */
  public function url($url)
  {
    $this->url = $url;
    $this->redirected = true;
  }
  
  /**
   * @return string
   */
  public function getUrl()
  {
    return $this->url;
  }
  
  /**
   * @return boolean
   */
  public function hasParameters()
  {
    return (count($this->parameters) > 0);
  }
  
  /**
   * HTTP Redirect to another location.
   *
   * @param string $to
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
}
