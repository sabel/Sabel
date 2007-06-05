<?php

/**
 * Sabel_Request
 *
 * @category   Request
 * @package    org.sabel.request
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Request_Web implements Sabel_Request
{
  /**
   * @var Sabel_Request_Uri $uri
   */
  protected $uri = null;
  
  /**
   * @var Sabel_Map_Candidate $candidate
   */
  protected $candidate = null;
    
  /**
   * @var Sabel_Request_Parameters $parameters
   */
  protected $parameters = null;
  
  private $method = Sabel_Request::GET;
  
  private $posts = array();
  
  public function __construct($requestUri = "", $method = null)
  {
    if ($method === null) {
      if (isset($_SERVER["REQUEST_METHOD"])) {
        $this->method = $_SERVER["REQUEST_METHOD"];
      }
    } else {
      $this->method = $method;
    }
    
    if (isset($_POST)) {
      $this->posts = $_POST;
    }
    
    $uriAndParams = $this->createRequestUri($requestUri);
    $parameters = (isset($uriAndParams["query"])) ? $uriAndParams["query"] : "";
    
    $uri = ltrim($uriAndParams["path"], "/");
    
    $this->uri        = new Sabel_Request_Uri($uri);
    $this->parameters = new Sabel_Request_Parameters($parameters);
  }
  
  public function parseUri($uri)
  {
    $uriAndParams = $this->createRequestUri($uri);
    $parameters = (isset($uriAndParams["query"])) ? $uriAndParams["query"] : "";
    $uri = ltrim($uriAndParams["path"], "/");
    
    $this->uri        = new Sabel_Request_Uri($uri);
    $this->parameters = new Sabel_Request_Parameters($parameters);
    
    return $this;
  }
    
  public function parameters($params)
  {
    $this->parameters = new Sabel_Request_Parameters($params);
    return $this;
  }
  
  protected function createRequestUri($requestUri)
  {
    $argv = isset($_SERVER["argv"]{0}) ? $_SERVER["argv"]{0} : null;
    
    if ($argv !== null && strpos($argv, "sabel") !== false) {
      $args = $_SERVER["argv"];
      array_shift($args);
      return join("/", $args);
    }
    
    if ($requestUri === "") {
      return parse_url($_SERVER["REQUEST_URI"]);
    } else {
      return parse_url($requestUri);
    }
  }
  
  public function hasParameters()
  {
    if (!is_object($this->parameters)) return false;
    if (!$this->parameters->isEmpty()) return false;
    return true;
  }
  
  public function hasParameter($name)
  {
    return (isset($this->parameters)) ? $this->parameters->hasA($name) : null;
  }
  
  public function getParameter($name)
  {
    if ($this->candidate !== null && $this->candidate->hasElementVariableByName($name)) {
      return $this->candidate->getElementVariableByName($name);
    } elseif (is_object($this->getParameters()) && $this->hasParameter($name)) {
      return $this->parameters->get($name);
    } else {
      return $this->getPostValue($name);
    }
  }
  
  /**
   * get parameters object
   *
   * @return Sabel_Request_Parameters
   */
  public function getParameters()
  {
    return $this->parameters;
  }
  
  public function setCandidate(Sabel_Map_Candidate $candidate)
  {
    $this->candidate = $candidate;
  }
  
  public function getPostValue($key)
  {
    if (isset($this->posts[$key])) {
      return $this->posts[$key];
    } else {
      return null;
    }
  }
  
  public function getPostRequests($postValues = null)
  {
    if ($postValues === null) $postValues = $_POST;
    
    if (get_magic_quotes_gpc()) {
      array_map("normalize", $postValues);
    }
        
    return $postValues;
  }
  
  public function getRequestUri()
  {
    return $this->uri;
  }
  
  public function __toString()
  {
    return $this->uri->__toString();
  }
  
  public function toArray()
  {
    return explode("/", $this->uri->__toString());
  }
  
  /**
   * remove non alphabet and non numeric characers.
   *
   * @param mixed  $target string or array
   * @param string $expected
   * @return mixed cleaned string or array
   */
  protected function removeNonAlphaNumeric(&$target, $expected = '')
  {
    $cleaned = null;
    
    if(is_array($target)) {
      foreach ($target as $key => $value) {
        $cleaned[$key] = preg_replace( "/[^\${$expected}a-zA-Z0-9]/", '', $value);
      }
    } else {
      $cleaned = preg_replace( "/[^\${$expected}a-zA-Z0-9]/", '', $target);
    }
    
    return $cleaned;
  }
  
  protected function normalize($target)
  {
    return normailize($target);
  }
}

function normalize($target)
{
  if (is_array($target)) {
    foreach ($target as &$value) $value = stripslashes($value);
  } else {
    $target = stripslashes($target);
  }
  return $target;
}
