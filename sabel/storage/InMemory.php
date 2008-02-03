<?php

/**
 * Storage of session
 *
 * @category   Storage
 * @package    org.sabel.storage
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2002-2006 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Storage_InMemory extends Sabel_Storage_Abstract
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
    
    $this->started = true;
    $this->initialize();
  }
  
  public function destroy()
  {
    $attributes = $this->attributes;
    $this->attributes = array();
    return $attributes;
  }
}
