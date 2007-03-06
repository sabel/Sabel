<?php

class Sabel_Form_Option extends Sabel_Form_HtmlElement
{
  protected $contents = '';
  protected $label    = '';
  protected $selected = false;
  
  const START_TAG_OPEN  = '<option';
  const START_TAG_CLOSE = '>';
  const END_TAG_FMT     = '</option>';
  
  public function __construct($contents, $value = '', $selected = false, 
                              $label = '', $id = '', $class = '', $style = '')
  {
    parent::__construct('', $value, $id, $class, $style);
    $this->contents = $contents;
    $this->label    = $label;
    $this->selected = $selected;
  }
  
  public function selected()
  {
    $this->selected = (!$this->selected);
  }
  
  public function isSelected()
  {
    return $this->selected;
  }
  
  public function getValue()
  {
    return $this->value;
  }
  
  public function getContents()
  {
    return $this->contents;
  }
  
  public function toHtml()
  {
    $buf = array();
    
    $value = ($this->value === '') ? $this->contents : $this->value;
    $buf[] = self::START_TAG_OPEN;
    
    if ($this->isSelected()) $buf[] = ' selected="selected"';
    $buf[] = sprintf(' value="%s"', $value);
    if ($this->label !== '') $buf[] = sprintf(' label="%s"', $this->label);
    if ($this->id !== '') $buf[] = sprintf(' id="%s"', $this->id);
    if ($this->class !== '') $buf[] = sprintf(' class="%s"', $this->class);
    
    $buf[] = self::START_TAG_CLOSE;
    
    $buf[] = $this->contents;
    
    $buf[] = self::END_TAG_FMT;
    return join("", $buf);
  }
}
