<?php

/**
 * Sabel_Cache_File
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Cache_File
{
  private $path = '';
  
  public function __construct($path)
  {
    $this->path = $path;
  }
  
  public function read($key)
  {
    if ($this->isReadable($key)) return file_get_contents($this->path);
  }
  
  public function write($value)
  {
    file_put_contents($this->path, $value);
  }
  
  public function isReadable($key)
  {
    return (is_readable($this->path));
  }
  
  public function append($value)
  {
    $fp = fopen($this->path, 'a+');
    if (!$fp) return null;
    $this->writeToFile($fp, $value);
    fclose($fp);
  }
  
  protected function writeToFile($fp, $value)
  {
    if (is_array($value)) {
      foreach ($value as $v) fwrite($fp, $v);
    } else {
      fwrite($fp, $value);
    }
  }
}