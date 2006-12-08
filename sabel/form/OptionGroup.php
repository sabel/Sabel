<?php

class Sabel_Form_OptionGroup extends Sabel_Form_HtmlElement
{
  const START_TAG_FMT = '<optgroup label="%s">';
  const END_TAG_FMT   = '</optgroup>';
  
  protected $options = array();
  
  public function addOption($option)
  {
    $this->options[] = $option;
  }
  
  protected function hasOptions()
  {
    return (count($this->options) > 0);
  }
  
  public function toHtml($trim = false)
  {
    $buf = array();
    
    $buf[] = sprintf(self::START_TAG_FMT, $this->name);
    if ($this->hasOptions()) {
      $options = $this->options;
      foreach ($options as $option) $buf[] = $option->toHtml();
    }
    $buf[] = self::END_TAG_FMT;
    
    return ($trim) ? join("", $buf) : join("\n", $buf);
  }
}
