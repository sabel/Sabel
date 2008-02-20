<?php

/**
 * Sabel_Token_Storage
 *
 * @interface
 * @category   Token
 * @package    org.sabel.token
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
interface Sabel_Token_Storage
{
  /**
   * @param string $key
   *
   * @return mixed
   */
  public function fetch($key);
  
  /**
   * @param string $key
   * @param mixed  $value
   *
   * @return void
   */
  public function store($key, $value);
  
  /**
   * @param string $key
   *
   * @return boolean
   */
  public function has($key);
  
  /**
   * @return void
   */
  public function clear($key);
}
