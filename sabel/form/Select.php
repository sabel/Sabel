<?php

class Sabel_Form_Select extends Sabel_Form_HtmlElement
{
  const START_TAG_FMT = '<select name="%s">';
  const END_TAG_FMT   = '</select>';
  
  protected $options = array();
  protected $optionsByName = array();
  protected $optionGroups = array();
  protected $multiple = false;
  
  public function multiple()
  {
    $this->multiple = (!$this->multiple);
  }
  
  public function isMultiple()
  {
    return $this->multiple;
  }
  
  public function addOption($option)
  {
    $this->options[] = $option;
    $this->optionsByValue[$option->getContents()] = $option;
  }
  
  public function addOptionGroup($og)
  {
    $this->optionGroups[] = $og;
  }
  
  public function toHtml($trim = false)
  {
    $buf = array();
    $buf[] = sprintf(self::START_TAG_FMT, $this->name);
    if ($this->hasOptions()) {
      $options = $this->options;
      foreach ($options as $option) $buf[] = $option->toHtml($trim);
    }
    
    if ($this->hasOptionGroups()) {
      $groups = $this->optionGroups;
      foreach ($groups as $group) $buf[] = $group->toHtml($trim);
    }
    $buf[] = self::END_TAG_FMT;
    
    return ($trim) ? join("", $buf) : join("\n", $buf);
  }
  
  protected function hasOptionGroups()
  {
    return (count($this->optionGroups) > 0);
  }
  
  protected function hasOptions()
  {
    return (count($this->options) > 0);
  }
}