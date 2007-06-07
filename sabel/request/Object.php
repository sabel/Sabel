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
  const ST_NO_INIT = 0;
  const ST_INIT    = 1;
  const ST_SET_URI = 2;
  const ST_SET_PARAM = 3;
  
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
  
  public function to($uri)
  {
    $this->uri = new Sabel_Request_Uri($uri);
    $this->status = self::ST_SET_URI;
    return $this;
  }
  
  public function parameter($parameters)
  {
    $this->parameters = new Sabel_Request_Parameters($parameters);
    $this->status = self::ST_SET_PARAM;
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
    $this->values[$key] = $value;
    return $this;
  }
  
  public function values($lists)
  {
    $this->values = array_merge($lists, $this->values);
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
  
  public function setGetValues($values)
  {
    $this->getValues = $values;
  }
  
  public function setPostValues($values)
  {
    $this->postValues = $values;
  }
  
  public function setParameterValues($values)
  {
    $this->parameterValues = $values;
  }
  
  public function isPost()
  {
    return ($this->httpMethod === Sabel_Request::POST);
  }
  
  public function isGet()
  {
    return ($this->httpMethod === Sabel_Request::GET);
  }
  
  public function isPut()
  {
    return ($this->httpMethod === Sabel_Request::PUT);
  }
  
  public function isDelete()
  {
    return ($this->httpMethod === Sabel_Request::DELETE);
  }
  
  public function getMethod()
  {
    return $this->httpMethod;
  }
  
  public function getUri()
  {
    return $this->uri;
  }
  
  public function __toString()
  {
    return $this->uri->__toString() ."?". $this->parameters->__toString();
  }
  
  public function toArray()
  {
    return $this->uri->toArray();
  }
}
