<?php

/**
 * Sabel_Request_Uri
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Request_Uri
{
  protected $request = '';
  
  /**
   * @var array parts of uri. separate by slash (/)
   */
  protected $parts = array();
  protected $entry = null;
  
  protected $parameters = null;
  
  
  public function __construct($requestUri = null, $entry = null)
  {
    $this->entry   = $entry; 
    $this->request = ($requestUri) ? $requestUri : self::getUri();
    
    if ($this->hasUriParameters()) {
      $uriAndParameters = explode('?', $this->request);
      $this->parts = explode('/', $uriAndParameters[0]);
      $this->parameters = new Sabel_Request_Parameters($uriAndParameters[1]);
    } else {
      $parts = explode('/', $this->request);
      
      foreach ($parts as $part) {
        if (!empty($part)) $this->parts[] = $part;
      }
    }
  }
  
  protected function hasUriParameters()
  {
    if (strpos($this->request, '?')) {
      $uriAndParameters = explode('?', $this->request);
      return (empty($uriAndParameters[1])) ? false : true;
    } else {
      return false;
    }
  }
  
  public static function getUri()
  {
    if (isset($_SERVER['argv']{0}) && strpos($_SERVER['argv']{0}, 'sabel') !== false) {
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
    return (isset($this->parts[$pos])) ? $this->parts[$pos] : null;
  }
  
  public function getByName($name)
  {
    $position = $this->entry->getUri()->calcElementPositionByName($name);
    return $this->get($position);
  }
  
  public function has($pos)
  {
    return isset($this->parts[$pos]);
  }
  
  public function hasParameters()
  {
    return (isset($this->parameters));
  }
  
  public function getParameters()
  {
    return $this->parameters;
  }
}