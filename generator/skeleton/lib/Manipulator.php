<?php

/**
 * Manipulator
 *
 * @category   DB
 * @package    lib.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Manipulator extends Sabel_DB_Manipulator
{
  public function before($method)
  {
    /* example.
    $method = "before" . ucfirst($method);
    if (method_exists($this, $method)) {
      return $this->$method();
    }
    */
  }
  
  public function after($method, $result)
  {
    
  }
}
