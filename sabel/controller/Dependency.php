<?php

class Sabel_Controller_Dependency
{
  protected $controller = null;
  
  public function setController($controller)
  {
    $this->controller = $controller;
  }
}