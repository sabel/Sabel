<?php

require_once('Sabel/Sabel.php');
require_once('classes.php');

$dt = new DirectoryTraverser(dirname(__FILE__) . '/skeleton');
$dt->visit(new SabelDirectoryAndFileCreator());
$dt->traverse();

class DirectoryTraverser
{
  protected $dir = '';
  protected $directories = null;
  protected $visitors = array();
  
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
      $entry = ltrim(str_replace($this->dir, '', $child), '/');
      if ($this->isValidDirectory($e)) {
        foreach ($this->visitors as $visitor) {
          $visitor->accept($entry, 'dir');
        }
        $this->traverse(new DirectoryIterator($child));
      } elseif ($this->isValidFile($e)) {
        foreach ($this->visitors as $visitor) {
          $visitor->accept($entry, 'file', $child);
        }
      }
    }
  }
  
  protected function isValidDirectory($element)
  {
    return ($element->isDir() && strpos($element->getFileName(), '.') === false);
  }
  
  protected function isValidFile($element)
  {
    return ($element->isFile() && strpos($element->getFileName(), '.') !== 0);
  }
}