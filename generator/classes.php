<?php

class SabelDirectoryAndFileCreator
{
  public function accept($element, $type, $child = null)
  {
    if (defined('TEST_CASE')) $element = RUN_BASE . $element;
    
    if ($type === 'dir') {
      if (is_dir($element)) {
        $this->printMessage("${element} already exists.");
      } else {
        $this->printMessage("create ${element}");
        mkdir($element);
      }
    } else if ($type === 'file') {
      if (is_file($element)) {
        $this->printMessage("${element} already exists.");
      } else {
        $this->printMessage("create ${element}");
        fwrite(fopen($element, 'w'), file_get_contents($child));
        if ($element == 'logs/sabel.log') {
          @chown('logs/sabel.log', 'www');
          chmod('logs/sabel.log', 0777);
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
    if (defined('TEST_CASE')) $element = RUN_BASE . $element;
    
    if ($type === 'dir') {
      if (!@rmdir($element)) $this->stack[] = $element;
    } else if ($type === 'file') {
      unlink($element);
    }
  }
  
  public function removeEmptyDirectories()
  {
    foreach (array_reverse($this->stack) as $path) rmdir($path);
  }
}