<?php

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
    if ($element->getFileName() === '.htaccess') return true;
    return ($element->isFile() && strpos($element->getFileName(), '.') !== 0);
  }
}

class SabelDirectoryAndFileCreator
{
  protected $overwrite = false;
  
  public function __construct($overwrite)
  {
    $this->overwrite = $overwrite;
  }
  
  public function accept($element, $type, $child = null)
  {
    if (defined('TEST_CASE')) $element = RUN_BASE . '/' . $element;
    
    if ($type === 'dir') {
      if (is_dir($element)) {
        $this->printMessage("${element} already exists.");
      } else {
        $this->printMessage("create ${element}");
        mkdir($element);
        if (in_array($element, array('data', 'cache'))) {
          chmod($element, 0777);
        }
      }
    } elseif ($type === 'file') {
      if (!$this->overwrite && is_file($element)) {
        $this->printMessage("${element} already exists.");
      } else {
        $this->printMessage("create ${element}");
        fwrite(fopen($element, 'w'), file_get_contents($child));
        if ($element == 'logs/sabel.log') {
          chmod($element, 0777);
        }
      }
    }
  }
  
  protected function printMessage($msg)
  {
    if (!defined('TEST_CASE')) echo $msg."\n";
  }
}

class SabelDirectoryAndFileRemover
{
  private $stack = array();
  
  public function accept($element, $type, $child = null)
  {
    if (defined('TEST_CASE')) $element = RUN_BASE  . '/' . $element;
    
    if ($type === 'dir') {
      if (!@rmdir($element)) $this->stack[] = $element;
    } elseif ($type === 'file') {
      unlink($element);
    }
  }
  
  public function removeEmptyDirectories()
  {
    foreach (array_reverse($this->stack) as $path) rmdir($path);
  }
}