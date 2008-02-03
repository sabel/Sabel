<?php

/**
 * scaffold creator
 *
 * @category   Task
 * @package    org.sabel.task
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2002-2006 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class SabelScaffold
{
  protected $ignores   = array();
  protected $overwrite = array();
  
  protected $targetDir     = "";
  protected $skeletonDir   = "";
  protected $dirnameLength = "";
  
  public function __construct($args, $skeletonDir)
  {
    $this->targetDir = $this->getTargetDir($args);
    $this->readOptions($args);
    
    $this->skeletonDir   = $skeletonDir;
    $this->dirnameLength = strlen($skeletonDir);
  }
  
  public function create($dir = null)
  {
    if ($dir === null) $dir = $this->skeletonDir;
    
    foreach (scandir($dir) as $item) {
      if ($item{0} === "." && $item !== ".htaccess") continue;
      
      $fullPath   = $dir . DS . $item;
      $targetItem = substr($fullPath, $this->dirnameLength + 1);
      $targetPath = $this->targetDir . DS . $targetItem;
      
      if (is_dir($fullPath)) {
        if (isset($this->ignore[$targetItem])) {
          Sabel_Command::message("ignore '{$targetItem}'.");
        } else {
          if (is_dir($targetPath)) {
            Sabel_Command::warning("'{$targetItem}' already exists.");
          } else {
            Sabel_Command::success("create $targetItem");
            mkdir ($targetPath);
          }
          
          $this->create($fullPath);
        }
      } else {
        if (isset($this->ignore[$targetItem])) {
          Sabel_Command::message("ignore '{$targetItem}'.");
        } elseif (is_file($targetPath) && !isset($this->overwrite[$targetItem])) {
          Sabel_Command::warning("'{$targetItem}' already exists.");
        } else {
          if (isset($this->overwrite[$targetItem])) {
            Sabel_Command::message("overwrite '{$targetItem}'");
            unlink($targetPath);
          }
          
          Sabel_Command::success("create $targetItem");
          copy($fullPath, $targetPath);
        }
      }
    }
  }
  
  public function chmod()
  {
    $dirs = array("cache", "data", "data" . DS . "compiled", "logs");
    
    foreach ($dirs as $dir) {
      chmod($this->targetDir . DS . $dir, 0777);
    }
  }
  
  protected function getTargetDir($args)
  {
    if (in_array("-d", $args, true)) {
      $dir = $args[array_search("-d", $args) + 1];
    } else {
      $dir = getcwd();
    }
    
    if (!is_dir($dir)) mkdir($dir);
    return $dir;
  }
  
  protected function readOptions($args)
  {
    if (in_array("--overwrite", $args, true)) {
      $index = array_search("--overwrite", $args) + 1;
      for ($i = $index, $c = count($args); $i < $c; $i++) {
        if (substr($args[$i], 0, 2) === "--") break;
        $this->overwrite[$args[$i]] = 1;
      }
    }
    
    if (in_array("--ignore", $args, true)) {
      $index = array_search("--ignore", $args) + 1;
      for ($i = $index, $c = count($args); $i < $c; $i++) {
        if (substr($args[$i], 0, 2) === "--") break;
        $this->ignore[$args[$i]] = 1;
      }
    }
  }
}

if (!defined("TEST_CASE")) {
  if (!defined("DS")) define("DS", DIRECTORY_SEPARATOR);
  $sabel = dirname(__FILE__) . DS . ".." . DS . "sabel";
  require ($sabel . DS . "Object.php");
  require ($sabel . DS . "Command.php");
  require ($sabel . DS . "Environment.php");
  
  $scaffold = new SabelScaffold($_SERVER["argv"], dirname(__FILE__) . DS . "skeleton");
  $scaffold->create();
  $scaffold->chmod();
}
