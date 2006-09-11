<#php

class Controllers_<? echo ucfirst($controllerName) ?> extends Sabel_Controller_Page
{
  public function initialize()
  {
    
  }
  
  public function index()
  {
    echo "welcome to <? echo ucfirst($controllerName) ?> controller. \n";
  }
}