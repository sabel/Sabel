<?php

/**
 * Sabel_Request_Object
 *
 * @category   Request
 * @package    org.sabel.request
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Request_Object extends Sabel_Object implements Sabel_Request
{
  /**
   * @var string
   */
  private $uri = "";
  
  /**
   * @var const Sabel_Request
   */
  private $method = Sabel_Request::GET;
  
  /**
   * @var array
   */
  private $getValues = array();
  
  /**
   * @var array
   */
  private $postValues = array();
  
  /**
   * @var array
   */
  private $parameterValues = array();
  
  /**
   * @var array
   */
  private $httpHeaders = array();
  
  public function __construct($uri = "")
  {
    $this->uri = $uri;
  }
  
  public function setUri($uri)
  {
    $this->uri = $uri;
    
    return $this;
  }
  
  public function getUri()
  {
    return $this->uri;
  }
  
  /**
   * get request
   *
   * @param string $uri
   */
  public function get($uri)
  {
    return $this->method(Sabel_Request::GET)->setUri($uri);
  }
  
  /**
   * post request
   *
   * @param string $uri
   */
  public function post($uri)
  {
    return $this->method(Sabel_Request::POST)->setUri($uri);
  }
  
  /**
   * put request
   *
   * @param string $uri
   */
  public function put($uri)
  {
    return $this->method(Sabel_Request::PUT)->setUri($uri);
  }
  
  /**
   * delete request
   *
   * @param string $uri
   */
  public function delete($uri)
  {
    return $this->method(Sabel_Request::DELETE)->setUri($uri);
  }
  
  public function method($method)
  {
    $this->method = $method;
    
    return $this;
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
  
  public function values(array $lists)
  {
    if ($this->isPost()) {
      $this->setPostValues(array_merge($this->postValues, $lists));
    } else {
      $this->setGetValues(array_merge($this->getValues, $lists));
    }
    
    return $this;
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
  
  public function setGetValues(array $values)
  {
    $this->getValues = $values;
  }
  
  public function fetchGetValues()
  {
    if (count($this->getValues) === 0) return array();
    
    foreach ($this->getValues as &$value) {
      if ($value === "") $value = null;
    }
    
    return $this->getValues;
  }
  
  public function hasGetValue($name)
  {
    return isset($this->getValues[$name]);
  }
  
  public function isGetSet($name)
  {
    return (isset($this->getValues[$name]) && $this->getValues[$name] !== "");
  }
  
  public function fetchGetValue($key)
  {
    if (isset($this->getValues[$key])) {
      $value = $this->getValues[$key];
      return ($value === "") ? null : $value;
    } else {
      return null;
    }
  }
  
  public function setPostValue($key, $value)
  {
    $this->postValues[$key] = $value;
  }
  
  public function setPostValues(array $values)
  {
    $this->postValues = $values;
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
    if (count($this->postValues) === 0) return array();
    
    foreach ($this->postValues as &$value) {
      if ($value === "") $value = null;
    }
    
    return $this->postValues;
  }
  
  public function setParameterValue($key, $value)
  {
    $this->parameterValues[$key] = $value;
    
    return $this;
  }
  
  public function setParameterValues(array $values)
  {
    $this->parameterValues = $values;
    
    return $this;
  }
  
  public function fetchParameterValue($key)
  {
    if (isset($this->parameterValues[$key])) {
      $value = $this->parameterValues[$key];
      return ($value === "") ? null : $value;
    } else {
      return null;
    }
  }
  
  public function fetchParameterValues()
  {
    if (count($this->parameterValues) === 0) return array();
    
    foreach ($this->parameterValues as &$value) {
      if ($value === "") $value = null;
    }
    
    return $this->parameterValues;
  }
  
  public function find($key)
  {
    if (empty($key)) return null;
    
    $result = null;
    $values = array($this->fetchPostValues(),
                    $this->fetchGetValues(),
                    $this->fetchParameterValues());
    
    foreach ($values as $value) {
      if (isset($value[$key])) {
        if ($result !== null) {
          throw new Sabel_Exception_Runtime("duplicate request key.");
        } else {
          $result = $value[$key];
        }
      }
    }
    
    return ($result === "") ? null : $result;
  }
  
  public function setHttpHeaders(array $headers)
  {
    $this->httpHeaders = $headers;
  }
  
  public function getHttpHeader($name)
  {
    $key = strtoupper("http_" . str_replace("-", "_", $name));
    return (isset($this->httpHeaders[$key])) ? $this->httpHeaders[$key] : null;
  }
  
  public function getHttpHeaders()
  {
    return $this->httpHeaders;
  }
  
  public function getExtension()
  {
    $parts = explode("/", $this->uri);
    $lastPart = array_pop($parts);
    
    if (($pos = strpos($lastPart, ".")) === false) {
      return "";
    } else {
      return substr($lastPart, $pos + 1);
    }
  }
}
