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
  private $uri = "";
  private $values = array(); 
  private $method = Sabel_Request::GET;
  
  public function to($uri)
  {
    $this->uri = $uri;
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
  
  public function getMethod()
  {
    return $this->httpMethod;
  }
  
  public function getUri()
  {
    return $this->uri;
  }
}
