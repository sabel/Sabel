<?php

/**
 * Sabel_Logger_File
 *
 * @category   Logger
 * @package    org.sabel.logger
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2002-2006 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Logger_File extends Sabel_Object
{
  const DEFAULT_LOG_DIR  = "logs";
  const DEFAULT_LOG_FILE = "sabel.log";
  
  private static $instance = null;
  
  private $fileName = "";
  private $contents = array();
  
  public static function singleton($fileName = null)
  {
    if (self::$instance === null) {
      self::$instance = new self($fileName);
    }
    
    return self::$instance;
  }
  
  public function __construct($fileName = null)
  {
    if ($fileName === null) {
      $this->fileName = $this->getLogFileName();
    } else {
      $this->fileName = $fileName;
    }
  }
  
  public function write($text, $level = LOG_INFO, $fileName = null)
  {
    if (ENVIRONMENT === PRODUCTION && $level !== LOG_ERR) return;
    
    $fmt = '%s [%s] %s';
    $this->contents[] = sprintf($fmt, now(), $this->defineToString($level), $text);
  }
  
  public function log($text, $level = LOG_INFO, $fileName = null)
  {
    $this->write($text, $level, $fileName);
  }
  
  public function __destruct()
  {
    $base = RUN_BASE . DS . self::DEFAULT_LOG_DIR . DS;
    $fp   = fopen($base . $this->fileName, "a");
    $sep  = "============================================================" . PHP_EOL;
    
    fwrite($fp, PHP_EOL . $sep . PHP_EOL);
    fwrite($fp, implode(PHP_EOL, $this->contents) . PHP_EOL);
  }
  
  private function getLogFileName()
  {
    if ($this->fileName !== "") {
      return $this->fileName;
    }
    
    if (!defined("ENVIRONMENT")) {
      $name = "test";
    } else {
      switch (ENVIRONMENT) {
        case PRODUCTION:
          $name = "production";
          break;
        case DEVELOPMENT:
          $name = "development";
          break;
        default:
          $name = "test";
      }
    }
    
    return $name . "." . self::DEFAULT_LOG_FILE;
  }
  
  private function defineToString($level)
  {
    switch ($level) {
      case LOG_INFO:
        return "info";
      case LOG_DEBUG:
        return "debug";
      case LOG_ERR:
        return "error";
    }
  }
}
