<?php

class Sabel_View_Helper_Markdown
{
  private $text = '';
  
  public function __construct($text)
  {
    $this->text = $text;
    return $this;
  }
  
  public function execute()
  {
    $pat = "@\[markdown\](.+?)\[/markdown\]@is";
    return preg_replace_callback($pat, array($this, 'markdown'), $this->text);
  }
  
  public function markdown($matches)
  {
    return Markdown($matches[1]);
  }
}
