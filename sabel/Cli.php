<?php

/**
 * Sabel_Cli
 *
 * @category   Cli
 * @package    org.sabel.cli
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Cli
{
  const MSG_INFO = 0x01;
  const MSG_WARN = 0x02;
  const MSG_MSG  = 0x04;
  const MSG_ERR  = 0x08;
  
  private static $headers = array(self::MSG_INFO => "[\x1b[1;32mSUCCESS\x1b[m]",
                                  self::MSG_WARN => "[\x1b[1;35mWARNING\x1b[m]",
                                  self::MSG_MSG  => "[\x1b[1;34mMESSAGE\x1b[m]",
                                  self::MSG_ERR  => "[\x1b[1;31mFAILURE\x1b[m]");
                                  
  private static $winHeaders = array(self::MSG_INFO => "[SUCCESS]",
                                     self::MSG_WARN => "[WARNING]",
                                     self::MSG_MSG  => "[MESSAGE]",
                                     self::MSG_ERR  => "[FAILURE]");
                                     
  public static function success($msg)
  {
    echo self::getHeader(self::MSG_INFO) . " $msg" . PHP_EOL;
  }
  
  public static function warning($msg)
  {
    echo self::getHeader(self::MSG_WARN) . " $msg" . PHP_EOL;
  }
  
  public static function message($msg)
  {
    echo self::getHeader(self::MSG_MSG) . " $msg" . PHP_EOL;
  }
  
  public static function error($msg)
  {
    echo self::getHeader(self::MSG_ERR) . " $msg" . PHP_EOL;
  }
  
  private static function getHeader($type)
  {
    if (!defined("IS_WIN") || IS_WIN) {
      return self::$winHeaders[$type];
    } else {
      return self::$headers[$type];
    }
  }
}
