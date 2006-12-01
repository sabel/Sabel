<?php

Sabel::using('Sabel_Env_Server');

/**
 * Sabel_Request
 *
 * @category   Request
 * @package    org.sabel.request
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Request
{
  protected static $server = null;
  
  /**
   * @var Sabel_Request_Uri object
   */
  protected $uri = null;
  
  /**
   * @var Sabel_Request_Parameters object
   */
  protected $parameters = null;
  
  public function __construct($entry = null, $requestUri = null)
  {
    if (self::$server === null) self::$server = Sabel_Env_Server::create();
    
    if (isset($requestUri)) $this->initializeRequestUriAndParameters($requestUri);
    if (isset($entry)) $this->initialize($entry);
  }
  
  public function initialize($entry)
  {
    if (is_object($this->uri)) {
      $this->uri->setEntry($entry);
    } else {
      throw new Sabel_Exception_Runtime('uri property must be object.');
    }
  }
  
  public function initializeRequestUriAndParameters($requestUri = null)
  {
    if (is_object($this->uri)) return null;
    
    $request_uri = "";
    
    if ($requestUri) {
      $request_uri = ltrim($requestUri, '/');
    } else {
      if (isset($_SERVER['argv']{0}) && strpos($_SERVER['argv']{0}, 'sabel') !== false) {
        $args = $_SERVER['argv'];
        array_shift($args);
        $request_uri = join('/', $args);
      } else {
        if (isset($_SERVER['REQUEST_URI']))
          $request_uri = ltrim($_SERVER['REQUEST_URI'], '/');
      }
    }
    
    $uriAndParams = explode('?', $request_uri);
    
    $this->uri = Sabel::load('Sabel_Request_Uri', $uriAndParams[0]);
    
    if (isset($uriAndParams[1])) {
      $this->parameters = Sabel::load('Sabel_Request_Parameters', $uriAndParams[1]);
    }
  }
  
  public function __get($name)
  {
    return $this->uri->$name;
  }
  
  public function hasUriValue($name)
  {
    $value = $this->uri->$name;
    return (isset($value)) ? $value : false;
  }
  
  public function hasMethod($name)
  {
    $ref = new ReflectionClass($this);
    return $ref->hasMethod($name);
  }
  
  public function getUri()
  {
    return $this->uri;
  }

  public function hasParameters()
  {
    if (!is_object($this->parameters)) return false;
    if (!$this->parameters->isEmpty()) return false;
    return true;
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
    return (self::$server->isMethod('POST')) ? true : false;
  }
  
  public function isGet()
  {
    return (self::$server->isMethod('GET')) ? true : false;
  }
  
  public function isPut()
  {
    return (self::$server->isMethod('PUT')) ? true : false;
  }
  
  public function isDelete()
  {
    return (self::$server->isMethod('DELETE')) ? true : false;
  }
  
  public function requests()
  {
    $array = array();
    foreach ($_POST as $key => $value) {
      $array[$key] = (isset($value)) ? Sanitize::normalize($value) : null;
    }
    return $array;
  }
  
  public function __toString()
  {
    return $this->requestUri;
  }
}