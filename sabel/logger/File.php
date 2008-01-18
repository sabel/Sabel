<?php

/**
 * Sabel_Logger_File
 *
 * @category   Logger
 * @package    org.sabel.logger
 * @author     Mori Reo <mori.reo@gmail.com>
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Logger_File extends Sabel_Object
{
  const DEFAULT_LOG_DIR  = "logs";
  const DEFAULT_LOG_FILE = "sabel.log";
  
  private static $instance = null;
  
  private $fileName = "";
  private $handlers = array();
  
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
    
    $this->open($this->fileName);
  }
  
  public function write($text, $level = LOG_INFO, $fileName = null)
  {
    $fmt = '%s [%s] %s' . PHP_EOL;
    $fp  = $this->open($fileName);
    
    if (ENVIRONMENT === PRODUCTION && $level !== LOG_ERR) return;
    fwrite($fp, sprintf($fmt, now(), $this->defineToString($level), $text));
  }
  
  public function log($text, $level = LOG_INFO, $fileName = null)
  {
    $this->write($text, $level, $fileName);
  }
  
  public function close($fileName = null)
  {
    if ($fileName === null) {
      $fileName = $this->getLogFileName();
    }
    
    if ($this->isOpend($fileName)) {
      fclose($this->handlers[$fileName]);
      unset($this->handlers[$fileName]);
    }
  }
  
  public function isOpend($fileName)
  {
    $h = $this->handlers;
    return (isset($h[$fileName]) && is_resource($h[$fileName]));
  }
  
  public function open($fileName = null)
  {
    if ($fileName === null) {
      $fileName = $this->getLogFileName();
    }
    
    if ($this->isOpend($fileName)) {
      return $this->handlers[$fileName];
    } else {
      $base = RUN_BASE . DS . self::DEFAULT_LOG_DIR . DS;
      $this->handlers[$fileName] = fopen($base . $fileName, "a");
      $sep = "============================================================";
      fwrite($this->handlers[$fileName], PHP_EOL . $sep . PHP_EOL . PHP_EOL);
      return $this->handlers[$fileName];
    }
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
