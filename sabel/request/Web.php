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
  
  protected $httpMethod = "get";
  
  protected $posts = array();
  
  private $result = null;
  private $fController = null;
  
  /**
   * @var Sabel_Request_Parameters $parameters
   */
  protected $parameters = null;
  
  public function __construct($requestUri = "", $httpMethod = null)
  {
    if ($httpMethod === null) {
      if (isset($_SERVER["REQUEST_METHOD"])) {
        $this->httpMethod = $_SERVER["REQUEST_METHOD"];
      }
    } else {
      $this->httpMethod = $httpMethod;
    }
    
    if (isset($_POST)) {
      $this->posts = $_POST;
    }
    
    $uriAndParams = explode("?", $this->createRequestUri($requestUri));
    $parameters = (isset($uriAndParams[1])) ? $uriAndParams[1] : "";
    
    $this->uri        = new Sabel_Request_Uri($uriAndParams[0]);
    $this->parameters = new Sabel_Request_Parameters($parameters);
  }
  
  public function parseUri($uri)
  {
    $this->to($uri);
  }
  
  public function to($uri)
  {
    $uriAndParams = explode("?", $this->createRequestUri($uri));
    $parameters = (isset($uriAndParams[1])) ? $uriAndParams[1] : "";
    $this->uri        = new Sabel_Request_Uri($uriAndParams[0]);
    $this->parameters = new Sabel_Request_Parameters($parameters);
    
    return $this;
  }
  
  public function parameters($params)
  {
    $this->parameters = new Sabel_Request_Parameters($params);
    return $this;
  }
  
  public function method($httpMethod)
  {
    $this->httpMethod = $httpMethod;
    return $this;
  }
  
  public function value($key, $value)
  {
    $this->setPostValue($key, $value);
    return $this;
  }
  
  public function values($lists)
  {
    $this->setPostValues($lists);
    return $this;
  }
  
  protected function createRequestUri($requestUri)
  {
    if ($requestUri !== "") return ltrim($requestUri, "/");

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
      return $this->getPostValue($name);
    }
  }
  
  public function setPostValue($name, $value)
  {
    $this->posts[$name] = $value;
    return $this;
  }
  
  public function setPostValues($values)
  {
    $this->posts = array_merge($values, $this->posts);
    return $this;
  }
  
  public function getPostValue($name)
  {
    return (isset($this->posts[$name])) ? $this->posts[$name] : null;
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
    return ($this->httpMethod === "POST");
  }
  
  public function isGet()
  {
    return ($this->httpMethod === "GET");
  }
  
  public function isPut()
  {
    return ($this->httpMethod === "PUT");
  }
  
  public function isDelete()
  {
    return ($this->httpMethod === "DELETE");
  }
  
  public function getHttpMethod()
  {
    return $this->httpMethod;
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
  
  public function get($uri)
  {
    return $this->method("GET")->to($uri);
  }
  
  public function post($uri)
  {
    return $this->method("POST")->to($uri);
  }
  
  public function launch($storage = null)
  {
    $aFrontController = new Sabel_Controller_Front();
    
    $aFrontController->processCandidate($this);
    
    $aFrontController->plugin
                     ->add(new Sabel_Controller_Plugin_Volatile($storage))
                     ->add(new Sabel_Controller_Plugin_Filter())
                     ->add(new Sabel_Controller_Plugin_Model())
                     ->add(new Sabel_Controller_Plugin_View())
                     ->add(new Sabel_Controller_Plugin_ExceptionHandler());

    $aFrontController->ignition($storage);
    $this->fController = $aFrontController;
    $this->fController->getResult();
    return $this;
  }
  
  public function isRedirected()
  {
    return $this->fController->getController()->isRedirected();
  }
  
  public function controller()
  {
    return $this->fController->getController();
  }
  
  public function result()
  {
    return $this->fController->getController();
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
