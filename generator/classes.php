<?php

class SabelDirectoryAndFileCreator
{  
  protected $overwrite = false;
  protected $ignores = array();
  
  public function __construct()
  {
  }
  
  public function setOverwrite($condition)
  {
    if (is_bool($condition)) $this->overwrite = $condition;
  }
  
  public function addIgnore($ignore)
  {
    $this->ignores[] = $ignore;
  }
  
  public function accept($element, $type, $child = null)
  {
    $element = RUN_BASE . DIR_DIVIDER . $element;
    
    if ($this->isIgnore($element)) {
      $this->printMessage("[\x1b[1;34mIGNORE\x1b[m] ignore: ${element}");
      return;
    }
    
    if ($type === Sabel_Util_DirectoryTraverser::TYPE_DIR) {
      if (is_dir($element)) {
        $this->printMessage("[\x1b[1;31mFAILURE\x1b[m] ${element} already exists.");
      } else {
        $this->printMessage("[\x1b[1;32mSUCCESS\x1b[m] create: ${element}");
        mkdir($element);
        if (in_array($element, array("data", "cache", "data/compiled"))) {
          if (chmod($element, 0777)) {
            $this->printMessage("[\x1b[1;32mSUCCESS\x1b[m] chmod {$element}");
          } else {
            $this->printMessage("[\x1b[1;31mFAILURE\x1b[m] chmod {$element}");
          }
        }
      }
    } elseif ($type === Sabel_Util_DirectoryTraverser::TYPE_FILE) {
      if (!$this->overwrite && is_file($element)) {
        $this->printMessage("[\x1b[1;31mFAILURE\x1b[m] ${element} already exists.");
      } else {
        $this->printMessage("[\x1b[1;32mSUCCESS\x1b[m] create: ${element}");
        fwrite(fopen($element, 'w'), file_get_contents($child));
        if ($element == "logs/sabel.log") {
          chmod($element, 0777);
        }
      }
    }
  }
  
  protected function isIgnore($name)
  {
    return (in_array($name, $this->ignores));
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