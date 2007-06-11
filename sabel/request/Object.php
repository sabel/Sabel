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
  const ST_NO_INIT   = 0;
  const ST_SET_URI   = 2;
  const ST_SET_PARAM = 4;
  
  private $status = self::ST_NO_INIT;
  
  /**
   * @var Sabel_Request_Uri
   */
  private $uri = null;
  
  /**
   * @var Sabel_Request_Parameters
   */
  private $parameters = null;
  
  private
    $getValues       = array(),
    $postValues      = array(),
    $parameterValues = array();
    
  private
    $method = Sabel_Request::GET;
    
  private
    $candidate = null;
    
  public static function newInstance()
  {
    return new self();
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
    $this->status     = self::ST_SET_PARAM;
    
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
  
  public function setGetValue($key, $value)
  {
    $this->getValues[$key] = $value;
  }
  
  public function setGetValues($values)
  {
    $this->getValues = $values;
  }
  
  public function getGetValues()
  {
    if (count($this->getValues) === 0) return null;
    return $this->getValues;
  }
  
  public function getGetValue($key)
  {
    if (array_key_exists($key, $this->getValues)) {
      return $this->getValues[$key];
    } else {
      return null;
    }
  }
  
  public function setPostValues($values)
  {
    $this->postValues = $values;
  }
  
  public function getPostValue($key)
  {
    if (array_key_exists($key, $this->postValues)) {
      return $this->postValues[$key];
    } else {
      return null;
    }
  }
  
  public function getPostValues()
  {
    if (count($this->postValues) === 0) return null;
    return $this->postValues;
  }
  
  public function setParameterValues($values)
  {
    $this->parameterValues = $values;
  }
  
  public function getParameterValue($key)
  {
    $this->initializeParameterValues();
    if (array_key_exists($key, $this->parameterValues)) {
      return $this->parameterValues[$key];
    } else {
      return null;
    }
  }
  
  public function getParameterValues()
  {
    $this->initializeParameterValues();
    if (count($this->parameterValues) === 0) return null;
    return $this->parameterValues;
  }
  
  private function initializeParameterValues()
  {
    if (count($this->parameterValues) !== 0) return;
    
    if (is_object($this->candidate)) {
      $this->parameterValues = $this->candidate->getElementVariables();
    }
  }
  
  public function find($key)
  {
    $result = null;
    $values = array($this->getPostValues(),
                    $this->getGetValues(),
                    $this->getParameterValues());
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
    
    return $result;
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
    return $this->uri->toArray();
  }
}
