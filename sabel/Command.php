<?php

/**
 * Sabel_Command
 *
 * @category   Sakle
 * @package    org.sabel.sakle
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Command
{
  const MSG_INFO = 0x01;
  const MSG_WARN = 0x02;
  const MSG_MSG  = 0x04;
  const MSG_ERR  = 0x08;
  
  private
    $stdin = null,
    $ends  = array("exit", "quit", "\q");
    
  private static $headers = array(self::MSG_INFO => "[\x1b[1;32m%s\x1b[m]",
                                  self::MSG_WARN => "[\x1b[1;35m%s\x1b[m]",
                                  self::MSG_MSG  => "[\x1b[1;34m%s\x1b[m]",
                                  self::MSG_ERR  => "[\x1b[1;31m%s\x1b[m]");
                                  
  private static $winHeaders = array(self::MSG_INFO => "[%s]",
                                     self::MSG_WARN => "[%s]",
                                     self::MSG_MSG  => "[%s]",
                                     self::MSG_ERR  => "[%s]");
                                     
  public static function success($msg)
  {
    echo self::getHeader(self::MSG_INFO, "SUCCESS") . " $msg" . PHP_EOL;
  }
  
  public static function warning($msg)
  {
    echo self::getHeader(self::MSG_WARN, "WARNING") . " $msg" . PHP_EOL;
  }
  
  public static function message($msg)
  {
    echo self::getHeader(self::MSG_MSG, "MESSAGE") . " $msg" . PHP_EOL;
  }
  
  public static function error($msg)
  {
    echo self::getHeader(self::MSG_ERR, "FAILURE") . " $msg" . PHP_EOL;
  }
  
  public static function hasOption($opt, $arguments)
  {
    return in_array("-" . $opt, $arguments, true);
  }
  
  public static function getOption($opt, &$arguments, $unset = true)
  {
    if (self::hasOption($opt, $arguments)) {
      $index  = array_search("-" . $opt, $arguments, true);
      $optVal = $arguments[$index + 1];
      
      if ($unset) {
        unset($arguments[$index]);
        unset($arguments[$index + 1]);
        $arguments = array_values($arguments);
      }
      
      return $optVal;
    } else {
      return null;
    }
  }
  
  public static function getHeader($type, $headMsg)
  {
    if (!defined("IS_WIN") || IS_WIN) {
      return sprintf(self::$winHeaders[$type], $headMsg);
    } else {
      return sprintf(self::$headers[$type], $headMsg);
    }
  }
  
  public function __construct($ends = null)
  {
    if ($ends !== null) {
      if (is_array($ends)) {
        $this->ends = $ends;
      } else {
        $message = "argument must be an array.";
        throw new Sabel_Exception_InvalidArgument($message);
      }
    }
    
    $this->stdin = fopen("php://stdin", "r");
    $endCommands = implode(" or ", $this->ends);
    echo "please input $endCommands to finish." . PHP_EOL . PHP_EOL;
  }
  
  public function read($message, $trim = true)
  {
    echo $message . ": ";
    
    $input = fgets($this->stdin);
    $input = ($trim) ? trim($input) : $input;
    
    if (in_array($input, $this->ends, true)) {
      return false;
    } else {
      return $input;
    }
  }
  
  public function quit()
  {
    fclose($this->stdin);
  }
}
