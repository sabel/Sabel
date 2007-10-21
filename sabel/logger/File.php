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
  
  private $fp = null;
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
        switch (ENVIRONMENT) {
          case PRODUCTION:
            $env = "production";
            break;
          case TEST:
            $env = "test";
            break;
          case DEVELOPMENT:
            $env = "development";
            break;
        }
        
        $fileName = $env . "." . self::DEFAULT_LOG_FILE;
      }
    }
    
    $this->path = RUN_BASE . DIR_DIVIDER
                . self::DEFAULT_LOG_DIR . DIR_DIVIDER . $fileName;
                
    $this->fp = fopen($this->path, "a+");
  }
  
  public function log($text, $level = LOG_INFO)
  {
    fwrite($this->fp, date("Y-m-d H:i:s") ." ". $text . "\n");
  }
}
