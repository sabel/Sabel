<?php

interface Logger
{
  public function log($text);
}

class FileLogger implements Logger
{
  const LOG_FILE = 'logs/sabel.log';
  
  private $fp = null;
  
  public function __construct()
  {
    $this->fp = fopen(self::LOG_FILE, 'a+');
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