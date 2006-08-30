<#php

require_once(RUN_BASE.'/app/index/models/<? echo ucfirst($controllerName) ?>.php');

class Index_<? echo ucfirst($controllerName) ?> extends Sabel_Controller_Page
{
  public function index()
  {
    echo "welcome to <? echo ucfirst($controllerName) ?> controller. \n";
  }
  
  public function lists()
  {
    $model = new <? echo ucfirst($controllerName) ?>();
    $model->setConstraint('order', 'id asc');
    Re::set('<? echo $controllerName ?>s', $model->select());
  }
  
  public function show()
  {
    $model = new <? echo ucfirst($controllerName) ?>($this->request->id);
    Re::set('<? echo $controllerName ?>', $model);
  }
  
  public function create()
  {
    $model = new <? echo ucfirst($controllerName) ?>();
    Re::set('<? echo $controllerName ?>', $model);
    
    if ($this->isPost()) {
      $model = new <? echo $controllerName ?>();
      $model->save($this->request->requests());
      $this->redirect('/index/<? echo $controllerName ?>/lists');
    }
  }
  
  public function edit()
  {
    $model = new <? echo ucfirst($controllerName) ?>($this->request->id);
    
    if ($this->isPost()) {
      $model->save($this->request->requests());
      $this->redirect('/index/<? echo $controllerName ?>/lists');
    }
    
    Re::set('<? echo $controllerName ?>', $model);
  }
  
  public function delete()
  {
    $model = new <? echo ucfirst($controllerName) ?>($this->request->id);
    $model->remove();
    $this->redirect('/index/<? echo $controllerName ?>/lists');
  }
}