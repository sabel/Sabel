<?php

/**
 * Sabel_Logger_File
 *
 * @category   Logger
 * @package    org.sabel.logger
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Logger_File implements Sabel_Logger_Interface
{
  const DEFAULT_LOG_DIR  = "logs";
  const DEFAULT_LOG_FILE = "sabel.log";
  
  private $handlers = array();
  private $messages = array();
  private $path = "";
  
  private static $instance = null;
  
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
      $fileName = $this->getLogFileName();
    }
    
    $this->open($fileName);
  }
  
  private function open($fileName = null)
  {
    if ($fileName === null) {
      $fileName = $this->getLogFileName();
    }
    
    $handlers =& $this->handlers;
    
    if (isset($handlers[$fileName]) && is_resource($handlers[$fileName])) {
      return $handlers[$fileName];
    } else {
      $base = RUN_BASE . DS . self::DEFAULT_LOG_DIR . DS;
      $handlers[$fileName] = fopen($base . $fileName, "a");
      return $handlers[$fileName];
    }
  }
  
  public function log($text, $level = LOG_INFO, $fileName = null)
  {
    $fmt = '%s [%s] %s' . PHP_EOL;
    fwrite($this->open(), sprintf($fmt, now(), $this->defineToString($level), $text));
  }
  
  private function getLogFileName()
  {
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
