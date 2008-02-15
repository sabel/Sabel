<?php

/**
 * Sabel_Storage_Memory
 *
 * @category   Storage
 * @package    org.sabel.storage
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Storage_Memory implements Sabel_Storage_Interface
{
  /**
   * @var self
   */
  private static $instance = null;
  
  /**
   * @var array
   */
  protected $attributes = array();
  
  private function __construct()
  {
    
  }
  
  /**
   * @return self
   */
  public static function create()
  {
    if (self::$instance === null) {
      self::$instance = new self();
    }
    
    return self::$instance;
  }
  
  /**
   * @param string $key
   *
   * @return mixed
   */
  public function read($key)
  {
    if ($this->has($key)) {
      return $this->attributes[$key];
    } else {
      return null;
    }
  }
  
  /**
   * @param string $key
   * @param mixed  $value
   *
   * @return void
   */
  public function write($key, $value)
  {
    $this->attributes[$key] = $value;
  }
  
  /**
   * @param string $key
   *
   * @return mixed
   */
  public function delete($key)
  {
    if ($this->has($key)) {
      $value = $this->attributes[$key];
      unset($this->attributes[$key]);
      return $value;
    } else {
      return null;
    }
  }
  
  /**
   * @param string $key
   *
   * @return boolean
   */
  public function has($key)
  {
    return array_key_exists($key, $this->attributes);
  }
  
  /**
   * @return array
   */
  public function clear()
  {
    $attributes = $this->attributes;
    $this->attributes = array();
    return $attributes;
  }
}
