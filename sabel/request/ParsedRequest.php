<?php

class ParsedRequest
{
  private $request;
  
  private static $instance = null;
  
  public static function create($request = null)
  {
    if (!self::$instance) {
      self::$instance = new self();
    } else if (isset($request)) {
      return new self($request);
    }
    return self::$instance;
  }
  
  protected function __construct($request = null)
  {
    $this->request = $this->parse($request);
  }
  
  protected function parse($request)
  {
    if (empty($request)) {
      $uri = explode('url=', $_SERVER['QUERY_STRING']);
      $uri = $uri[1];
    } else {
      $uri = $request;
    }
    
    if (!empty($uri)) {
      $pattern = '/^([\w]+)?(?:\/([\w]+))?(?:\/([\w]+))?(?:\/?&([\w]+))?/';
      preg_match($pattern, $uri, $matches);

      array_shift($matches);
      return $matches;
    } else {
      return null;
    }
  }
  
  public function getModule()
  {
    if (!empty($this->request[0])) {
      return $this->request[0];
    } else {
      return Sabel_Core_Const::DEFAULT_MODULE;
    }
  }
  
  public function getController()
  {
    if (!empty($this->request[1])) {
      return $this->request[1];
    } else {
      return SabelConst::DEFAULT_CONTROLLER;
    }
  }
  
  public function getMethod()
  {
    if (!empty($this->request[2])) {
      return $this->request[2];
    } else {
      return SabelConst::DEFAULT_METHOD;
    }
  }
  
  public function getParameter()
  {
    if (!empty($this->request[3])) {
      return $this->request[3];
    } else {
      return null;
    }
  }
}

?>
