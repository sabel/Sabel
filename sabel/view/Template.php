<?php

/**
 * Sabel_View_Template
 *
 * @abstract
 * @category   View
 * @package    org.sabel.view
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_View_Template extends Sabel_Object
{
  protected $baseDir = "";
  protected $name    = "";
  
  abstract public function isValid();
  abstract public function getContents();
  abstract public function create($body = "");
  abstract public function delete();
  
  public function __construct($baseDir)
  {
    $this->baseDir = MODULES_DIR_PATH . DS . $baseDir;
  }
  
  public function name($name = null)
  {
    if ($name === null) {
      return $this->name;
    } elseif (is_string($name)) {
      $this->name = $name . TPL_SUFFIX;
    } else {
      $message = "argument must be a string.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
  }
  
  public function getPath()
  {
    return $this->baseDir . $this->name;
  }
}
