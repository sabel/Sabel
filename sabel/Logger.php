<?php

/**
 * Sabel_Logger
 *
 * @category   Logger
 * @package    org.sabel.logger
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Logger extends Sabel_Object
{
  const DEFAULT_LOG_FILE = "sabel.log";
  
  private static $instance = null;
  
  protected $filePath = "";
  protected $logger   = null;
  protected $messages = array();
  
  public static function create($fileName = null)
  {
    if (self::$instance === null) {
      self::$instance = new self($fileName);
    }
    
    return self::$instance;
  }
  
  public function __construct($fileName = null)
  {
    if ($fileName === null) {
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
      
      $this->filePath = LOG_DIR_PATH . DS . $name . "." . self::DEFAULT_LOG_FILE;
    } else {
      $this->filePath = LOG_DIR_PATH . DS . $fileName;
    }
  }
  
  public function setLogger(Sabel_Logger_Interface $logger)
  {
    $this->logger = $logger;
  }
  
  public function write($text, $level = SBL_LOG_INFO, $fileName = null)
  {
    if ((SBL_LOG_LEVEL & $level) === 0) return;
    
    $fmt = '%s [%s] %s';
    $this->messages[] = sprintf($fmt, now(), $this->defineToString($level), $text);
  }
  
  public function getMessages()
  {
    return $this->messages;
  }
  
  public function __destruct()
  {
    static $ran = false;
    if ($ran) return;
    
    $logger = ($this->logger === null) ? new Sabel_Logger_File() : $this->logger;
    $logger->output($this->filePath, $this->messages);
    
    $ran = true;
  }
  
  protected function defineToString($level)
  {
    switch ($level) {
      case SBL_LOG_INFO:
        return "info";
      case SBL_LOG_DEBUG:
        return "debug";
      case SBL_LOG_WARN:
        return "warning";
      case SBL_LOG_ERR:
        return "error";
    }
  }
}
