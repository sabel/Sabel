<?php

/**
 * Sabel_Redirector
 *
 * @category   Core
 * @package    org.sabel.core
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Redirector extends Sabel_Object
{
  /**
   * @var self
   */
  protected static $instance = null;
  
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
  
  private function __construct()
  {
    
  }
  
  public static function create()
  {
    if (self::$instance === null) {
      self::$instance = new self();
    }
    
    return self::$instance;
  }
  
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
    $this->parameters = $parameters;
    
    $candidate = Sabel_Context::getContext()->getCandidate();
    return $this->uri($candidate->uri($uriParameter));
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
    $this->redirected = true;
    
    if ($this->hasParameters()) {
      $this->uri = $uri . "?" . http_build_query($this->parameters, "", "&");
    } else {
      $this->uri = $uri;
    }
    
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
