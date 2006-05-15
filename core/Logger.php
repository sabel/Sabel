<?php

interface Logger
{
  public function log($text);
}

class FileLogger implements Logger
{
  const LOG_FILE = 'sabel.log';
  
  private $fp = null;
  
  public function __construct()
  {
    $this->fp = fopen(self::LOG_FILE, 'a+');
  }
  
  public function log($text)
  {
    fwrite($this->fp, $text);
  }
  
  public function __destruct()
  {
    fclose($this->fp);
  }
}

?>