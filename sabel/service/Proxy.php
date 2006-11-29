<?php

class Sabel_Service_Proxy
{
  protected $impl = null;
  
  public function __construct($impl = null)
  {
    $this->impl = $impl;
  }
  
  public function scond($arg1, $arg2 = null, $not = null)
  {
    $this->impl->scond($arg1, $arg2, $not);
  }
  
  public function select($param1 = null, $param2 = null, $param3 = null)
  {
    return $this->impl->select($param1, $param2, $param3);
  }
}

class Sabel_Service_Remote
{
  protected $request = null;
  protected $host = '';
  
  public function __construct($host)
  {
    $this->host = $host;
    $this->request = new Sabel_Http_Request();
  }
  
  public function scond($param1, $param2, $param3)
  {
    
  }
  
  public function select($param1 = null, $param2 = null, $param3 = null)
  {
    $params = serialize(array($param1, $param2, $param3));
    $response = $this->request->request($this->host, '/bbs/lists', array('params'=>$params));
    return unserialize($response->getContents());
  }
}