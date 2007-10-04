<?php

/**
 * Sabel_Request_Object
 *
 * @category   Request
 * @package    org.sabel.request
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Request_Object
{
  /**
   * @var status
   */
  const ST_NO_INIT   = 0;
  const ST_SET_URI   = 2;
  const ST_SET_PARAM = 4;
  
  private $status = self::ST_NO_INIT;
  
  private $variableHolder = array();
  
  /**
   * @var Sabel_Request_Uri
   */
  private $uri = null;
  
  /**
   * @var Sabel_Request_Parameters
   */
  private $parameters = null;
  
  /**
   * @var headers
   */
  private $headers = array();
  
  private
    $getValues       = array(),
    $postValues      = array(),
    $parameterValues = array();
    
  private
    $method = Sabel_Request::GET;
  
  public function __construct($uri = null)
  {
    if ($uri !== null) {
      $this->to($uri);
    } else {
      $this->uri = new Sabel_Request_Uri("");
    }
  }
  
  public function to($uri)
  {
    $this->uri    = new Sabel_Request_Uri($uri);
    $this->status = self::ST_SET_URI;
    
    return $this;
  }
  
  public function parameter($parameters)
  {
    $this->parameters = new Sabel_Request_Parameters($parameters);
    
    if (self::ST_SET_URI & $this->status) {
      $this->status = self::ST_SET_URI + self::ST_SET_PARAM;
    } else {
      $this->status = self::ST_SET_PARAM;
    }
    
    return $this;
  }
  
  /**
   * get request
   *
   * @param string $uri
   */
  public function get($uri)
  {
    return $this->method(Sabel_Request::GET)->to($uri);
  }
  
  /**
   * post request
   *
   * @param string $uri
   */
  public function post($uri)
  {
    return $this->method(Sabel_Request::POST)->to($uri);
  }
  
  /**
   * put request
   *
   * @param string $uri
   */
  public function put($uri)
  {
    return $this->method(Sabel_Request::PUT)->to($uri);
  }
  
  /**
   * delete request
   *
   * @param string $uri
   */
  public function delete($uri)
  {
    return $this->method(Sabel_Request::DELETE)->to($uri);
  }
  
  public function method($method)
  {
    $this->method = $method;
    return $this;
  }
  
  public function value($key, $value)
  {
    switch ($this->method) {
      case (Sabel_Request::GET):
        $this->setGetValue($key, $value);
        break;
      case (Sabel_Request::POST):
        $this->setPostValue($key, $value);
        break;
    }
    
    return $this;
  }
  
  public function values($lists)
  {
    switch ($this->method) {
      case (Sabel_Request::GET):
        $this->setGetValues(array_merge($lists, $this->getValues));
        break;
      case (Sabel_Request::POST):
        $this->setPostValues(array_merge($lists, $this->postValues));
        break;
      default:
        $this->setGetValues(array_merge($lists, $this->getValues));
    }
    
    return $this;
  }
  
  public function getValue($name)
  {
    if (isset($this->values[$name])) {
      $this->values[$name];
    } else {
      null;
    }
  }
  
  public function hasValueWithMethod($name)
  {
    if ($this->isPost()) {
      return ($this->hasPostValue($name));
    } elseif ($this->isGet()) {
      return ($this->hasGetValue($name));
    }
  }
  
  public function getValueWithMethod($name)
  {
    if ($this->hasValueWithMethod($name)) {
      if ($this->isPost()) {
        return $this->fetchPostValue($name);
      } elseif ($this->isGet()) {
        return $this->fetchGetValue($name);
      }
    } else {
      return null;
    }
  }
  
  public function setGetValue($key, $value)
  {
    $this->getValues[$key] = $value;
  }
  
  public function setGetValues($values)
  {
    $this->getValues = $values;
  }
  
  public function fetchGetValues()
  {
    if (count($this->getValues) === 0) return null;
    
    foreach ($this->getValues as &$value) {
      if ($value === "") $value = null;
    }
    
    return $this->getValues;
  }
  
  public function hasGetValue($name)
  {
    return (isset($this->getValues[$name]));
  }
  
  public function isGetSet($name)
  {
    return (isset($this->getValues[$name]) && $this->getValues[$name] !== "");
  }
  
  public function fetchGetValue($key)
  {
    if (array_key_exists($key, $this->getValues)) {
      return $this->getValues[$key];
    } else {
      return null;
    }
  }
  
  public function setPostValue($key, $value)
  {
    $this->postValues[$key] = $value;
  }
  
  public function setPostValues($values)
  {
    $this->postValues = $values;
  }
  
  public function fetchGetObject()
  {
    return (object) $this->fetchGetValues();
  }
  
  public function hasPostValue($name)
  {
    return isset($this->postValues[$name]);
  }
  
  public function isPostSet($name)
  {
    return (isset($this->postValues[$name]) && $this->postValues[$name] !== "");
  }
  
  public function fetchPostValue($key)
  {
    if (array_key_exists($key, $this->postValues)) {
      $value = $this->postValues[$key];
      return ($value === "") ? null : $value;
    } else {
      return null;
    }
  }
  
  public function fetchPostValues()
  {
    if (count($this->postValues) === 0) return null;
    
    foreach ($this->postValues as &$value) {
      if ($value === "") $value = null;
    }
    
    return $this->postValues;
  }
  
  public function fetchPostObject()
  {
    return (object) $this->fetchPostValues();
  }
  
  public function setParameterValue($key, $value)
  {
    $this->parameterValues[$key] = $value;
    return $this;
  }
  
  public function setParameterValues($values)
  {
    $this->parameterValues = $values;
    return $this;
  }
  
  public function fetchParameterValue($key)
  {
    if (array_key_exists($key, $this->parameterValues)) {
      $value = $this->parameterValues[$key];
      return ($value === "") ? null : $value;
    } else {
      return null;
    }
  }
  
  public function fetchParameterValues()
  {
    if (count($this->parameterValues) === 0) return null;
    return $this->parameterValues;
  }
  
  public function find($key)
  {
    $result = null;
    $values = array($this->fetchPostValues(),
                    $this->fetchGetValues(),
                    $this->fetchParameterValues());
    $found = false;
    
    foreach ($values as $value) {
      if (isset($value[$key])) {
        if ($found) {
          throw new Sabel_Exception_SecurityWarning("duplicate request key");
        } else {
          $result = $value[$key];
        }
        $found = true;
      }
    }
    
    return ($result === "") ? null : $result;
  }
  
  public function isPost()
  {
    return ($this->method === Sabel_Request::POST);
  }
  
  public function isGet()
  {
    return ($this->method === Sabel_Request::GET);
  }
  
  public function isPut()
  {
    return ($this->method === Sabel_Request::PUT);
  }
  
  public function isDelete()
  {
    return ($this->method === Sabel_Request::DELETE);
  }
  
  public function getMethod()
  {
    return $this->method;
  }
  
  public function getUri()
  {
    return $this->uri;
  }
  
  public function isTypeOf($type)
  {
    return ($this->uri->getType() === $type);
  }
  
  public function setCandidate($candidate)
  {
    $this->candidate = $candidate;
  }
  
  public function __toString()
  {
    if ($this->uri->size() === 0) {
      $uri = "/";
    } else {
      $uri = $this->uri->__toString();
    }
    
    if (is_object($this->parameters)) {
      if ($this->parameters->size() === 0) {
        return $uri;
      }
      return $uri . "?" . $this->parameters->__toString();
    } else {
      return $uri;
    }
  }
  
  public function toArray()
  {
    if ($this->status & self::ST_SET_URI) {
      return $this->uri->toArray();
    } else {
      return null;
    }
  }
  
  public function setVariable($key, $value)
  {
    $this->variableHolder[$key] = $value;
  }
  
  public function clearVariable()
  {
    $this->variableHolder = array();
  }
  
  public function setHeader($name, $value)
  {
    $this->headers[$name] = $value;
  }
  
  public function hasHeader($name)
  {
    return (array_key_exists($name, $this->headers));
  }
  
  public function getHeader($name)
  {
    if ($this->hasHeader($name)) {
      return $this->headers[$name];
    } else {
      return null;
    }
  }
  
  public function getHeaders()
  {
    return $this->headers;
  }
  
  public function __get($key)
  {
    if (array_key_exists($key, $this->variableHolder)) {
      return $this->variableHolder[$key];
    } else {
      return null;
    }
  }
}
