<?php

/**
 * cache to null
 *
 * @category   Cache
 * @package    org.sabel.cache
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Cache_Null
{
  public function read($key)
  {
    return null;
  }
  
  public function isReadable($key)
  {
    return false;
  }
  
  public function write($key, $value)
  {
    
  }
}