<?php

/**
 * Sabel_Util_DirectoryTraverser
 *
 * @category   Util
 * @package    org.sabel.util
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Util_DirectoryTraverser
{
  const TYPE_DIR  = "dir";
  const TYPE_FILE = "file";
  
  protected $dir         = "";
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
      $entry = ltrim(str_replace($this->dir, "", $child), DIRECTORY_SEPARATOR);
      
      if ($this->isValidDirectory($e)) {
        foreach ($this->visitors as $visitor) {
          $visitor->accept($entry, self::TYPE_DIR);
        }
        
        try {
          $this->traverse(new DirectoryIterator($child));
        } catch (Exception $e) {
          
        }
      } elseif ($this->isValidFile($e)) {
        foreach ($this->visitors as $visitor) {
          $visitor->accept($entry, self::TYPE_FILE, $child);
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
