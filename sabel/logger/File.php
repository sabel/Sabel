<?php

/**
 * Sabel_Logger_File
 *
 * @package    org.sabel.logger
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Logger_File
{
  const DEFAULT_LOG_PATH = '/logs';
  const DEFAULT_LOG_FILE = 'sabel.log';
  
  private $fp = null;
  private $messages = array();
  private static $instance = null;

  public static function singleton()
  {
    if (is_null(self::$instance)) self::$instance = new self();
    return self::$instance;
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
    
    $path = RUN_BASE . self::DEFAULT_LOG_PATH ."/" . $option;
    $this->fp = fopen($path, 'a+');
  }
  
  public function log($text)
  {
    $this->messages[] = date("c") ." ". $text . "\n";
  }
  
  public function __destruct()
  {
    $messages = $this->messages;
    foreach ($messages as $message) fwrite($this->fp, $message);
    fclose($this->fp);
  }
}