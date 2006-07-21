<?php

/**
 * Sabel_Request_URI
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Request_URI
{
  protected $request;
  
  /**
   * @var array parts of uri. separate by slash (/)
   */
  protected $parts;
  
  protected $parameters = null;
  
  public function __construct($requestUri = null)
  {
    $this->request = ($requestUri) ? $requestUri : self::getUri();
    if ($this->hasUriParameters()) {
      $uriAndParameters = explode('?', $this->request);
      $this->parts = explode('/', $uriAndParameters[0]);
      $this->parameters = new Sabel_Request_Parameters($uriAndParameters[1]);
    } else {
      $this->parts = explode('/', $this->request);
    }
  }
  
  protected function hasUriParameters()
  {
    if (strpos($this->request, '?')) {
      $uriAndParameters = explode('?', $this->request);
      if (!empty($uriAndParameters[1])) {
        return true;
      } else {
        return false;
      }
    } else {
      return false;
    }
  }
  
  public static function getUri()
  {
    if ($_SERVER['argv']{0} == './sabel.php' || $_SERVER['argv']{0} == 'sabel.php') {
      $args = $_SERVER['argv'];
      array_shift($args);
      $request_uri = join('/', $args);
    } else {
      $request_uri = ltrim($_SERVER['REQUEST_URI'], '/');
    }
    
    return $request_uri;
  }
  
  public function count()
  {
    return count($this->parts);
  }
  
  public function get($pos)
  {
    return $this->parts[$pos];
  }
  
  public function hasParameters()
  {
    return (!is_null($this->parameters));
  }
  
  public function getParameters()
  {
    return $this->parameters;
  }
}