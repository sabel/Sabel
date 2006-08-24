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
  
  // such as /module/controller/action/something
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
    $svr = Sabel_Env_Server::create();
    
    if ($requestUri) {
      $request_uri = ltrim($requestUri, '/');
    } else {
      $argv = $svr->argv;
      if (isset($argv[0]) && strpos($argv[0], 'sabel') !== false) {
        $args = $argv;
        array_shift($args);
        $request_uri = join('/', $args);
      } else {
        $request_uri = ($svr->request_uri) ? ltrim($svr->request_uri, '/') : null;
      }
    }
    
    // @todo test this.
    @list($this->requestUri, $this->requestParameters) = explode('?', $request_uri);
  }
  
  public function __get($name)
  {
    return $this->uri->$name;
  }
  
  public function getUri()
  {
    return $this->uri;
  }

  public function hasParameters()
  {
    return (!empty($this->requestParameters));
  }
  
  public function getParameters()
  {
    return $this->parameters;
  }
  
  public function isPost()
  {
    return (self::$server->isMethod('POST')) ? true : false;
  }
  
  public function isGet()
  {
    return (self::$server->isMethod('GET')) ? true : false;
  }
  
  public function isPut()
  {
    return (self::$server->isMethod('PUT')) ? true : false;
  }
  
  public function isDelete()
  {
    return (self::$server->isMethod('DELETE')) ? true : false;
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
