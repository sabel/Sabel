<?php $ucControllerName = ucfirst($controllerName) ?>

<#php

class Controllers_<? echo $ucControllerName ?> extends Sabel_Controller_Page
{
  /**
   * @implementation <? echo $ucControllerName ?>
   * @setter set<? echo $ucControllerName ?>
   */
  protected $<? echo $ucControllerName ?> = null;
  
  public function set<? echo $ucControllerName ?>($<? echo $ontrollerName ?>)
  {
    $this-><? echo $controllerName ?> = $<? echo $controllerName ?>;
  }
  
  public function lists()
  {
    $this-><? echo $controllerName ?>->select();
  }
  
  public function show()
  {
    $this-><? echo $controllerName ?>->choice($this->id);
  }
  
  public function create()
  {
    $this-><? echo $controllerName ?>->assign();
  }
  
  public function postCreate()
  {
    $this-><? echo $controllerName ?>->save($this->requests);
  }
  
  public function edit()
  {
    $this-><? echo $controllerName ?>->choice($this->id);
  }
  
  public function postEdit()
  {
    $this-><? echo $controllerName ?>->choice($this->id)->save($this->requests);
  }
  
  public function delete()
  {
    $this-><? echo $controllerName ?>->choice($this->id)->remove();
    $this->redirectToPrevious();
  }
}