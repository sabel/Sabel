<#php

class Index_<? echo ucfirst($controllerName) ?> extends Sabel_Controller_Page
{
  protected $<? echo $controllerName ?> = null;
  
  public function initialize()
  {
    $this-><? echo $controllerName ?> = new <? echo ucfirst($controllerName) ?>();
  }
  
  public function index()
  {
    echo "welcome to <? echo ucfirst($controllerName) ?> controller. \n";
  }
  
  public function lists()
  {
    Re::set('<? echo $controllerName ?>s', $this-><? echo $controllerName ?>->lists());
  }
  
  public function show()
  {
    Re::set('<? echo $controllerName ?>', $this-><? echo $controllerName ?>->show($this->id));
  }
  
  public function create()
  {
    $model = new <? echo ucfirst($controllerName) ?>();
    Re::set('<? echo $controllerName ?>', $model);
    
    if ($this->isPost()) {
      $model = new <? echo ucfirst($controllerName) ?>();
      $model->save($this->request->requests());
      $this->redirect(urlFor('default', 'lists'));
    }
  }
  
  public function edit()
  {
    $model = new <? echo ucfirst($controllerName) ?>($this->request->id);
    
    if ($this->isPost()) {
      $model->save($this->request->requests());
      $this->redirect(urlFor('default', 'lists'));
    }
    
    Re::set('<? echo $controllerName ?>', $model);
  }
  
  public function delete()
  {
    $model = new <? echo ucfirst($controllerName) ?>($this->request->id);
    $model->remove();
    $this->redirect(urlFor('default', 'lists'));
  }
}