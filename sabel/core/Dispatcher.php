<?php

class Sabel_Core_Dispatcher
{
  public function __construct()
  {
    
  }
  
  public function dispatch($destination)
  {
    return new Sabel_Controller_Null();
  }
}