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
class Sabel_Request_Object extends Sabel_Object implements Sabel_Request
{
  private $variableHolder = array();
  
  /**
   * @var Sabel_Request_Uri
   */
  private $uri = null;
  
  /**
   * @var Sabel_Request_Token
   */
  private $token = null;
  
  private
    $getValues       = array(),
    $postValues      = array(),
    $parameterValues = array();
    
  /**
   * @var const Sabel_Request
   */
  private $method = Sabel_Request::GET;
  
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
    $this->uri = new Sabel_Request_Uri($uri);
    
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
    if (array_key_exists($key, $this->parameterValues)) {
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
  
  public function setToken(Sabel_Request_Token $token)
  {
    $this->token = $token;
  }
  
  public function getToken()
  {
    if (is_object($this->token)) {
      return $this->token;
    } else {
      $this->token = new Sabel_Request_Token();
      $this->token->setValue($this->getValueWithMethod("token"));
      return $this->token;
    }
  }
  
  public function getUri()
  {
    return $this->uri;
  }
  
  public function isTypeOf($type)
  {
    return ($this->uri->type() === $type);
  }
  
  public function __toString()
  {
    if ($this->uri->size() === 0) {
      return "";
    } else {
      return $this->uri->toString();
    }
  }
  
  public function toArray()
  {
    return $this->uri->toArray();
  }
  
  public function setVariable($key, $value)
  {
    $this->variableHolder[$key] = $value;
  }
  
  public function clearVariable()
  {
    $this->variableHolder = array();
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
