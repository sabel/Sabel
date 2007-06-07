<?php

/**
 * Sabel_Request_Parameters
 *
 * @category   Request
 * @package    org.sabel.request
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Request_Parameters
{
  private $rawParameters = "";
  private $parsedParameters = array();
  
  public function __construct($parameters)
  {
    if (!empty($parameters)) {
      $this->rawParameters = $parameters;
      $parameters = str_replace("?", "", $parameters);
      $this->parse($parameters);
    }
  }
  
  /**
   * Parsing URL request
   *
   * @param array $parameter list of URI parts
   * @return void
   */
  public function parse($parameters)
  {
    $parameters = explode("&", $parameters);
    $sets = array();
    foreach ($parameters as $pair) {
      @list($key, $val) = explode("=", $pair);
      $sets[$key] = $val;
    }
    $this->parsedParameters = $sets;
    return $parameters;
  }
  
  public function isEmpty()
  {
    return ($this->rawParameters === "");
  }
  
  public function __get($key)
  {
    return $this->get($key);
  }
  
  public function get($key)
  {
    $pp = $this->parsedParameters;
    return (isset($pp[$key])) ? $pp[$key] : null;
  }
  
  public function set($key, $value)
  {
    $this->parsedParameters[$key] = $value;
  }
  
  public function hasA($key)
  {
    return (isset($this->parsedParameters[$key]));
  }
  
  public function __toString()
  {
    return $this->rawParameters;
  }
  
  public function toArray()
  {
    $this->parsedParameters;
  }
}
