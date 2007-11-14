<?php

/**
 * Sabel_Logger_File
 *
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
    if (ENVIRONMENT === PRODUCTION) {
      self::$instance = new Sabel_Logger_File();
    }
    
    if (is_object(self::$instance)) {
      return self::$instance;
    } else {
      self::$instance = new self($fileName);
      return self::$instance;
    }
  }
  
  public function __construct($fileName = null)
  {
    if ($fileName === null) {
      if (!defined("ENVIRONMENT")) {
        $fileName = "test." . self::DEFAULT_LOG_FILE;
      } else {
        $fileName = $this->getEnv() . "." . self::DEFAULT_LOG_FILE;
      }
    }
    
    $this->open($fileName);
  }
  
  private function open($fileName = null)
  {
    $base = RUN_BASE . DS . self::DEFAULT_LOG_DIR . DS;
    
    if ($fileName === null) {
      return $this->handlers[$this->getEnv() . "." . self::DEFAULT_LOG_FILE];
    }
    
    if (!isset($this->handlers[$fileName]) || !is_resource($this->handlers[$fileName])) {
      $this->handlers[$fileName] = fopen($base . $fileName, "a");
    }
      
    return $this->handlers[$fileName];
  }
  
  private function getEnv()
  {
    switch (ENVIRONMENT) {
      case PRODUCTION:
        return "production";
      case TEST:
        return "test";
      case DEVELOPMENT:
        return "development";
    }
  }
  
  public function log($text, $level = LOG_INFO, $fileName = null)
  {
    $fmt = '%s [%s] %s' . "\n";
    
    if ($fileName !== null) {
      $handler = $this->open($fileName);
      fwrite($handler, sprintf($fmt, date("Y-m-d H:i:s"), $this->defineToString($level), $text));
    } else {
      $handler = $this->open();
      fwrite($handler, sprintf($fmt, date("Y-m-d H:i:s"), $this->defineToString($level), $text));
    }
  }
  
  private function defineToString($level)
  {
    switch ($level) {
      case LOG_INFO:
        return "info";
      case LOG_DEBUG:
        return "debug";
    }
  }
}
