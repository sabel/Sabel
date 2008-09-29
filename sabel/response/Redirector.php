<?php

/**
 * Sabel_Response_Redirector
 *
 * @category   Response
 * @package    org.sabel.core
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Response_Redirector extends Sabel_Object
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
    $context = Sabel_Context::getContext();
    $uri = $context->getCandidate()->uri($uriParameter);
    
    if ($this->parameters = $parameters) {
      $uri .= "?" . http_build_query($this->parameters, "", "&");
    }
    
    return $this->uri($uri);
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
    $this->uri = "/" . ltrim($uri, "/");
    $this->redirected = true;
    
    return $this->uri;
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
