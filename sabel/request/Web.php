<?php

Sabel::using("Sabel_Request");

/**
 * Sabel_Request
 *
 * @category   Request
 * @package    org.sabel.request
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Request_Web extends Sabel_Object implements Sabel_Request
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
  
  public function __construct($requestUri = "")
  {
    $uriAndParams = explode('?', $this->createRequestUri($requestUri));
    $parameters = (isset($uriAndParams[1])) ? $uriAndParams[1] : "";
    
    $this->uri        = Sabel::load('Sabel_Request_Uri', $uriAndParams[0]);
    $this->parameters = Sabel::load('Sabel_Request_Parameters', $parameters);
    
    assert(is_object($this->uri));
    assert(is_object($this->parameters));
  }
  
  protected function createRequestUri($requestUri)
  {
    if ($requestUri !== "") return ltrim($requestUri, '/');

    $request_uri = "";    
    if (isset($_SERVER['argv']{0}) && strpos($_SERVER['argv']{0}, 'sabel') !== false) {
      $args = $_SERVER['argv'];
      array_shift($args);
      $request_uri = join('/', $args);
    } elseif (isset($_SERVER['REQUEST_URI'])) {
      $request_uri = ltrim($_SERVER['REQUEST_URI'], '/');
    }
    
    return $request_uri;
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
      return $this->getPost($name);
    }
  }
  
  public function getPost($name)
  {
    return (isset($_POST[$name])) ? $_POST[$name] : null;
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
  
  public function isPost()
  {
    return ($_SERVER['REQUEST_METHOD'] === 'POST');
  }
  
  public function isGet()
  {
    return ($_SERVER['REQUEST_METHOD'] === 'GET');
  }
  
  public function isPut()
  {
    return ($_SERVER['REQUEST_METHOD'] === 'PUT');
  }
  
  public function isDelete()
  {
    return ($_SERVER['REQUEST_METHOD'] === 'DELETE');
  }
  
  public function setCandidate(Sabel_Map_Candidate $candidate)
  {
    $this->candidate = $candidate;
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