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
  const DEFAULT_LOG_PATH = '/logs';
  const DEFAULT_LOG_FILE = 'sabel.log';
  
  private $fp = null;
  private $messages = array();
  private $path = "";
  
  private static $instance = null;
  
  public static function singleton($option = null)
  {
    if (ENVIRONMENT === PRODUCTION) {
      self::$instance = new Sabel_Logger_Null();
    }
    
    if (is_object(self::$instance)) {
      return self::$instance;
    } else {
      self::$instance = new self($option);
      return self::$instance;
    }
  }
  
  public function __construct($option = null)
  {
    if ($option === null) {
      switch (ENVIRONMENT) {
        case PRODUCTION:
          $env = 'production';
          break;
        case TEST:
          $env = 'test';
          break;
        case DEVELOPMENT:
          $env = 'development';
          break;
      }
      $option = $env . "." . self::DEFAULT_LOG_FILE;
    }
    
    $this->path = RUN_BASE . self::DEFAULT_LOG_PATH ."/" . $option;
    $this->fp = fopen($this->path, "w+");
  }
  
  public function log($text)
  {
    fwrite($this->fp, date("c") ." ". $text . "\n");
  }
  
  public function __destruct()
  {
  }
}