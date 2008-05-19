<?php

/**
 * Sabel_Controller_Redirector
 *
 * @category   Controller
 * @package    org.sabel.controller
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Controller_Redirector
{
  /**
   * @var string
   */
  protected $url = "";
  
  /**
   * @var string
   */
  protected $uri = "";
  
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
    $this->redirected = true;
    $this->parameters = $parameters;
    
    $candidate = Sabel_Context::getContext()->getCandidate();
    $this->uri($candidate->uri($uriParameter));
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
   * @param string $uri
   *
   * @return void
   */
  public function uri($uri)
  {
    if ($this->hasParameters()) {
      $this->uri = $uri . "?" . http_build_query($this->parameters, "", "&");
    } else {
      $this->uri = $uri;
    }
    
    $this->redirected = true;
  }
  
  public function getUri()
  {
    return $this->uri;
  }
  
  /**
   * @return boolean
   */
  public function hasParameters()
  {
    return (count($this->parameters) > 0);
  }
}
