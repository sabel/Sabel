<?php

/**
 * Storage of session
 *
 * @category   Storage
 * @package    org.sabel.storage
 * @author     Mori Reo <mori.reo@gmail.com>
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Storage_Session extends Sabel_Storage_Abstract
{
  private static $instance = null;
  
  public static function create()
  {
    if (self::$instance === null) {
      self::$instance = new self();
    }
    
    return self::$instance;
  }
  
  public function start()
  {
    if ($this->started) return;
    
    session_start();
    
    $this->started = true;
    $this->attributes =& $_SESSION;
    $this->initialize();
  }
  
  public function destroy()
  {
    $attributes = $this->attributes;
    session_destroy();
    
    return $attributes;
  }
}
