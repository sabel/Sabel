<?php

uses('sabel.core.Utility');

/**
 * Sabel_Request_Request
 * 
 * @package org.sabel.request
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Request_Request
{
  protected static $server = null;
  
  // such as /module/controller/action/something?param=val
  protected $requestUri = '';
  
  // such as param=val
  protected $requestParameters = '';
  
  protected
    $uri        = null,
    $parameters = null;
  
  public function __construct($entry, $requestUri = null)
  {
    if (!self::$server) self::$server = new Sabel_Env_Server();
    
    $this->initializeRequestUriAndParameters($requestUri);
    $this->uri        = new Sabel_Request_Uri($this->requestUri, $entry);
    $this->parameters = new Sabel_Request_Parameters($this->requestParameters);
  }
  
  private function initializeRequestUriAndParameters($requestUri)
  {
    if ($requestUri) {
      $request_uri = ltrim($requestUri, '/');
    } else {
      if (isset($_SERVER['argv']{0}) && strpos($_SERVER['argv']{0}, 'sabel') !== false) {
        $args = $_SERVER['argv'];
        array_shift($args);
        $request_uri = join('/', $args);
      } else {
        $request_uri = (isset($_SERVER['REQUEST_URI'])) ? ltrim($_SERVER['REQUEST_URI'], '/') : null;
      }
    }
    
    // @todo test this.
    @list($this->requestUri, $this->requestParameters) = explode('?', $request_uri);
  }
  
  public function getUri()
  {
    return $this->uri;
  }

  // @todo remove this??
  public function hasParameters()
  {
    return (isset($this->parameters));
  }
  
  public function getParameters()
  {
    return $this->parameters;
  }
  
  public function isPost()
  {
    return (self::$server->request_method === 'POST' && count($_POST) !== 0) ? true : false;
  }
  
  public function isGet()
  {
    return (self::$server->request_method === 'GET' && count($_GET) !== 0) ? true : false;
  }
  
  public function isPut()
  {
    return (self::$server->request_method === 'PUT') ? true : false;
  }
  
  public function isDelete()
  {
    return (self::$server->request_method === 'DELETE') ? true : false;
  }
  
  public function requests()
  {
    $array = array();
    foreach ($_POST as $key => $value) {
      $array[$key] = (isset($value)) ? Sanitize::normalize($value) : null;
    }
    return $array;
  }
}