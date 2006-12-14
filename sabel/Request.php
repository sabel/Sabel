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
class Sabel_Request
{
  protected $uri = null;
  
  /**
   * @var Sabel_Request_Parameters object
   */
  protected $parameters = null;
  
  public function __construct($requestUri = null)
  {
    $uriAndParams = explode('?', $this->createRequestUri($requestUri));
   
    $this->uri = Sabel::load('Sabel_Request_Uri', $uriAndParams[0]);
   
    if (isset($uriAndParams[1])) {
      $this->parameters = Sabel::load('Sabel_Request_Parameters', $uriAndParams[1]);
    }
  }
  
  protected function createRequestUri($requestUri)
  {
    if ($requestUri !== null) return ltrim($requestUri, '/');

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
  
  public function hasMethod($name)
  {
    $ref = new ReflectionClass($this);
    return $ref->hasMethod($name);
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
    if ($this->hasParameter($name)) {
      return $this->parameters->get($name);
    } else {
      return null;
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
  
  public function getRequestValue($name)
  {
    if (isset($_POST[$name])) {
      return $_POST[$name];
    } else {
      return null;
    }
  }
  
  public function requests()
  {
    $array = array();
    foreach ($_POST as $key => $value) {
      $array[$key] = (isset($value)) ? Sanitize::normalize($value) : null;
    }
    return $array;
  }
  
  public function getRequestUri()
  {
    return $this->uri;
  }
  
  public function __toString()
  {
    return $this->uri->__toString();
  }
}

class Sanitize
{
  /**
   * remove non alphabet and non numeric characers.
   *
   * @param mixed  $target string or array
   * @param string $expected
   * @return mixed cleaned string or array
   */
  public static function removeNonAlphaNumeric(&$target, $expected = '')
  {
    $cleaned = null;
    var_dump($target);
    
    if(is_array($target)) {
      foreach ($target as $key => $value) {
        $cleaned[$key] = preg_replace( "/[^\${$expected}a-zA-Z0-9]/", '', $value);
      }
    } else {
      $cleaned = preg_replace( "/[^\${$expected}a-zA-Z0-9]/", '', $target);
    }
    
    return $cleaned;
  }
  
  /**
   * target SQL string to make SQL safety
   *
   */
  public static function sqlSafe($target)
  {
    return addslashes($target);
  }
  
  public static function normalize($target)
  {
    if (!get_magic_quotes_gpc()) return $target;
    
    if (is_array($target)) {
      foreach ($target as &$value) $value = stripslashes($value);
    } else {
      $target = stripslashes($target);
    }
    return $target;
  }
}
