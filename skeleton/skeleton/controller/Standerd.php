<#php

require_once(RUN_BASE.'/app/index/models/<? echo ucfirst($controllerName) ?>.php');

class Index_<? echo $controllerName ?> extends Sabel_Controller_Page
{
  public function index()
  {
    echo "welcome to <? echo $controllerName ?> controller. \n";
  }
  
  public function lists()
  {
    
  }
  
  public function show()
  {
    $person = new <? echo ucfirst($controllerName) ?>($this->request->getByName('id'));
    Re::set('<? echo $controllerName ?>', $<? echo $controllerName ?>);
  }
  
  public function edit()
  {
    
  }
  
  public function delete()
  {
    
  }
}