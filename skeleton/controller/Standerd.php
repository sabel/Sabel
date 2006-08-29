<#php

require_once(RUN_BASE.'/app/index/models/<? echo ucfirst($controllerName) ?>.php');

class Index_<? echo ucfirst($controllerName) ?> extends Sabel_Controller_Page
{
  public function initialize()
  {
    Sabel_I18n_Gettext::init();
  }
  
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
    $model = new <? echo ucfirst($controllerName) ?>($this->request->getUri()->getByName('id'));
    Re::set('<? echo $controllerName ?>', $model);
  }
  
  public function create()
  {
    if ($this->request->isPost()) {
      $model = new <? echo $controllerName ?>($this->request->getUri()->getByName('id'));
      foreach ($this->request->requests() as $name => $value) {
        $model->$name = $value;
      }
      $model->save();
      $this->redirect('/index/<? echo $controllerName ?>/lists');
    }
  }
  
  public function edit()
  {
    $model = new <? echo ucfirst($controllerName) ?>($this->request->getUri()->getByName('id'));
    
    if ($this->request->isPost()) {
      foreach ($this->request->requests() as $name => $value) {
        $model->$name = $value;
      }
      $model->save();
      $this->redirect('/index/<? echo $controllerName ?>/lists');
    }
    
    Re::set('<? echo $controllerName ?>', $model);
  }
  
  public function delete()
  {
    $model = new <? echo ucfirst($controllerName) ?>($this->request->getUri()->getByName('id'));
    $model->remove();
    $this->redirect('/index/<? echo $controllerName ?>/lists');
  }
}