<?php

/**
 * Sabel_Storage_Interface
 *
 * @interface
 * @category   Storage
 * @package    org.sabel.storage
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2002-2006 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
interface Sabel_Storage_Interface
{
  /**
   * @param string $key
   *
   * @return mixed
   */
  public function read($key);
  
  /**
   * @param string $key
   * @param mixed  $value
   *
   * @return void
   */
  public function write($key, $value);
  
  /**
   * @param string $key
   *
   * @return mixed
   */
  public function delete($key);
  
  /**
   * @param string $key
   *
   * @return boolean
   */
  public function has($key);
  
  /**
   * @return array
   */
  public function clear();
}
