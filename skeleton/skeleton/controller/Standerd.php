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
    $id = $this->request->getByName('id');
    $person = new Person();
    Re::set('<? echo $controllerName ?>', $<? echo $controllerName ?>);
    Re::set('id', $id);
  }
  
  public function edit()
  {
    
  }
  
  public function delete()
  {
    
  }
}