<?php

 /**
  * Cache Interface
  *
  * @interface
  * @category   Cache
  * @package    org.sabel.cache
  * @author     Mori Reo <mori.reo@gmail.com>
  *             Ebine Yutaka <ebine.yutaka@gmail.com>
  * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
  * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
  */
interface Sabel_Cache_Interface
{
  public function read($key);
  public function write($key, $value, $timeout = 600, $comp = false);
  public function delete($key);
}
