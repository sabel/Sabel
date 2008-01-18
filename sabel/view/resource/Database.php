<?php

/**
 * Sabel_View_Resource_Database
 *
 * @category   View
 * @package    org.sabel.view
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_View_Resource_Database
  extends Sabel_Object implements Sabel_View_Resource_Interface
{
  private $path = "";
  
  public function __construct($path)
  {
    $this->path = $path;
  }
  
  public function getPath()
  {
    return $this->path;
  }
  
  public function fetch()
  {
    if ($this->isValid()) {
      // @todo
    } else {
      return false;
    }
  }
  
  public function isValid()
  {
    // @todo
  }
}
