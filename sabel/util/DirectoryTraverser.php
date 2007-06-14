<?php

class Sabel_Util_DirectoryTraverser
{
  protected $dir         = '';
  protected $directories = null;
  protected $visitors    = array();
  
  public function __construct($dir = null)
  {
    $this->dir = ($dir) ? $dir : dirname(realpath(__FILE__));
    $this->directories = new DirectoryIterator($this->dir);
  }
  
  public function visit($visitor)
  {
    $this->visitors[] = $visitor;
  }
  
  public function traverse(DirectoryIterator $fromElement = null)
  {
    $element = ($fromElement === null) ? $this->directories : $fromElement;
    foreach ($element as $e) {
      $child = $e->getPathName();
      $entry = ltrim(str_replace($this->dir, "", $child), DIR_DIVIDER);
      if ($this->isValidDirectory($e)) {
        foreach ($this->visitors as $visitor) {
          $visitor->accept($entry, "dir");
        }
        $this->traverse(new DirectoryIterator($child));
      } elseif ($this->isValidFile($e)) {
        foreach ($this->visitors as $visitor) {
          $visitor->accept($entry, "file", $child);
        }
      }
    }
  }
  
  protected function isValidDirectory($element)
  {
    return ($element->isDir() && strpos($element->getFileName(), ".") === false);
  }
  
  protected function isValidFile($element)
  {
    if ($element->getFileName() === ".htaccess") return true;
    return ($element->isFile() && strpos($element->getFileName(), ".") !== 0);
  }
}