<?php

class MockRequest extends Sabel_Request
{
  public function getPostRequests()
  {
    return array();
  }
  
  public function __toString()
  {
    
  }
  
  public function getParameters()
  {
    return new StdClass();
  }
  
  public function hasParameter($name)
  {
    return true;
  }
  
  public function getParameter($name)
  {
    return "testParam";
  }
  
  public function getHttpMethod()
  {
    return "GET";
  }
}