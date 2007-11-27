<?php

/**
 * Sabel_Sakle_Task
 *
 * @category   Sakle
 * @package    org.sabel.sakle
 * @author     Mori Reo <mori.reo@gmail.com>
 *             Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Sakle_Task extends Sabel_Object
{
  const MSG_INFO = 0x01;
  const MSG_WARN = 0x02;
  const MSG_MSG  = 0x04;
  const MSG_ERR  = 0x08;
  
  private static $headers = array(self::MSG_INFO => "[\x1b[1;32mSUCCESS\x1b[m]",
                                  self::MSG_WARN => "[\x1b[1;35mWARNING\x1b[m]",
                                  self::MSG_MSG  => "[\x1b[1;34mMESSAGE\x1b[m]",
                                  self::MSG_ERR  => "[\x1b[1;31mERROR\x1b[m]");
                                  
  private static $winHeaders = array(self::MSG_INFO => "[SUCCESS]",
                                     self::MSG_WARN => "[WARNING]",
                                     self::MSG_MSG  => "[MESSAGE]",
                                     self::MSG_ERR  => "[ERROR]");
                                     
  abstract public function run($arguments);
  
  protected function printMessage($msg, $type = self::MSG_INFO)
  {
    echo self::getHeader($type) . ": {$msg}\n";
  }
  
  public static function success($msg)
  {
    echo self::getHeader(self::MSG_INFO) . ": {$msg}\n";
  }
  
  public static function warning($msg)
  {
    echo self::getHeader(self::MSG_WARN) . ": {$msg}\n";
  }
  
  public static function message($msg)
  {
    echo self::getHeader(self::MSG_MSG) . ": {$msg}\n";
  }
  
  public static function error($msg)
  {
    echo self::getHeader(self::MSG_ERR) . ": {$msg}\n";
  }
  
  private static function getHeader($type)
  {
    $headers = (IS_WIN) ? self::$winHeaders : self::$headers;
    return $headers[$type];
  }
}
