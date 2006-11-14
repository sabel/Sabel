<?php

class Sabel_Form_HtmlElement
{
  protected $name  = '';
  protected $value = '';
  protected $id    = '';
  protected $class = '';
  protected $style = '';
  
  public function __construct($name, $value = '', $id = '', $class = '', $style = '')
  {
    $this->name  = $name;
    $this->value = $value;
    $this->id    = $id;
    $this->class = $class;
    $this->style = $style;
  }
}