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

    if(is_array($target)) {
      foreach ($target as $key => $value) {
        $cleaned[$key] = preg_replace( "/[^${$expected}a-zA-Z0-9]/", '', $value);
      }
    } else {
      $cleaned = preg_replace( "/[^${$expected}a-zA-Z0-9]/", '', $target);
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

  public static function normalize(&$target)
  {
    $cleaned = null;

    if (get_magic_quotes_gpc()) {
      if (is_array($target)) {
        foreach ($target as $key => $value) {
          $cleaned[$key] = stripslashes($value);
        }
      } else {
        $cleaned = stripslashes($target);
      }
    } else {
      $cleaned = $target;
    }

    return $cleaned;
  }
}