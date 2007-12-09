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
    $element = RUN_BASE . DS . $element;
    
    if ($this->isIgnore($element)) {
      $this->printMessage("[\x1b[1;34mIGNORE\x1b[m] ignore: ${element}");
      return;
    }
    
    if ($type === Sabel_Util_DirectoryTraverser::TYPE_DIR) {
      if (is_dir($element)) {
        Sabel_Cli::error("{$element} already exists.");
      } else {
        Sabel_Cli::success("create: {$element}");
        mkdir($element);
        $targets = array(RUN_BASE . DS . "data",
                         RUN_BASE . DS . "cache",
                         RUN_BASE . DS . "logs",
                         RUN_BASE . DS . "data" . DS . "compiled");
                         
        if (in_array($element, $targets)) {
          if (chmod($element, 0777)) {
            Sabel_Cli::success("chmod: {$element}");
          } else {
            Sabel_Cli::error("chmod: {$element}");
          }
        }
      }
    } elseif ($type === Sabel_Util_DirectoryTraverser::TYPE_FILE) {
      if (!$this->overwrite && is_file($element)) {
        Sabel_Cli::error("{$element} already exists.");
      } else {
        Sabel_Cli::success("create: {$element}");
        fwrite(fopen($element, "w"), file_get_contents($child));
        if ($element == RUN_BASE . DS . "logs/sabel.log" ||
            $element == RUN_BASE . DS . "config/connection.php") {
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
    if (!defined("TEST_CASE")) echo $msg . PHP_EOL;
  }
}

class SabelDirectoryAndFileRemover
{
  private $stack = array();
  
  public function accept($element, $type, $child = null)
  {
    if (defined("TEST_CASE")) {
      $element = RUN_BASE  . DS . $element;
    }
    
    if ($type === "dir") {
      if (!@rmdir($element)) $this->stack[] = $element;
    } elseif ($type === "file") {
      unlink($element);
    }
  }
  
  public function removeEmptyDirectories()
  {
    foreach (array_reverse($this->stack) as $path) rmdir($path);
  }
}
