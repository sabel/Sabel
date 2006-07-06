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
      //$uri = $_SERVER['REQUEST_URI'];
      $uri = explode('url=', $_SERVER['QUERY_STRING']);
      $uri = $uri[1];
    } else {
      $uri = $request;
    }
    
    $sp = split('/', $uri);
    //array_shift($sp);
    
    $request = array();
    foreach ($sp as $p => $v) {
      if (strpos($v, '&')) {
        $splited = split('&', $v);
        $request[2] = $splited[0];
        $request[3] = '?'.$splited[1];
      } else if (substr($v, 0, 1) == '&') {
        $request[3] = '?'.substr($v, 1);
      } else {
        $request[] = $v;
      }
    }
    
    return $request;
  }
  
  public function getModule()
  {
    if (!empty($this->request[0])) {
      return $this->request[0];
    } else {
      return SabelConst::DEFAULT_MODULE;
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
