<?php

class Sabel_Logger_File
{
  const LOG_FILE = '/logs/sabel.log';
  
  private $fp = null;
  private static $instance = null;

  public static function singleton()
  {
    if (is_null(self::$instance)) {
      self::$instance = new self();
    }
    return self::$instance;
  }
  
  private function __construct()
  {
    $this->fp = fopen(RUN_BASE . self::LOG_FILE, 'a+');
  }
  
  public function log($text)
  {
    $message = date(DATE_ATOM) . "\t" .$text . "\n";
    fwrite($this->fp, $message);
  }
  
  public function __destruct()
  {
    fclose($this->fp);
  }
}

?>
