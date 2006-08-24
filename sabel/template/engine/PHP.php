<?php

class Sabel_Template_Engine_PHP extends Sabel_Template_Engine
{
  protected $attributes;
  
  public function assign($key, $value)
  {
    $this->attributes[$key] = $value;
  }
  
  public function put($key)
  {
    echo $this->attributes[$key];
  }
  
  public function __get($key)
  {
    return $this->attributes[$key];
  }
  
  public function retrieve()
  {
    uses('sabel.db.schema.Accessor');
    $is = new Sabel_DB_Schema_Accessor('default', 'default');
    $table = $is->getTable('blog');
    
    if (count($this->attributes) != 0) extract($this->attributes, EXTR_SKIP);
    extract(Re::get(), EXTR_SKIP);
    ob_start();
    include($this->getTemplateFullPath());
    $content = ob_get_clean();
    return $content;
  }
  
  public function load_template($name)
  {
    $t = clone $this;
    $t->setTemplateName($name . '.tpl');
    echo $t->retrieve();
  }
  
  public function configuration()
  {
  }
  
  public function display()
  {
    if (is_file($this->tplpath . 'layout.tpl')) {
      $this->content_for_layout = $this->retrieve();
      $this->setTemplateName('layout.tpl');
    }
    echo $this->retrieve();
  }
}