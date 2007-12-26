<?php

/**
 * Interface for Session Stroage
 *
 * @interface
 * @category   Storage
 * @package    org.sabel.storage
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
interface Sabel_Storage_Interface
{
  public function start();
  public function isStarted();
  public function has($key);
  public function read($key);
  public function write($key, $value, $timeout = 0);
  public function delete($key);
  public function destroy();
  public function getTimeouts();
}
