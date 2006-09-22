<?php

/**
 * Sabel_Cache_File
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Cache_File
{
  private $prefix = '';
  
  public function __construct($prefix = null)
  {
    $this->prefix = (!is_null($prefix)) ? $prefix : '';
  }
  
  public function read($filePath)
  {
    $fp = fopen($this->prefix . $filePath, 'r+');
    if (!$fp) return null;
    $lines = array();
    while(!feof($fp)) {
      $lines[] = fgets($fp);
    }
    fclose($fp);
    return $lines;
  }
  
  public function write($filePath, $value)
  {
    $fp = fopen($this->prefix . $filePath, 'w+');
    if (!$fp) return null;
    $this->writeToFile($fp, $value);
    fclose($fp);
  }
  
  public function append($filePath, $value)
  {
    $fp = fopen($this->prefix . $filePath, 'a+');
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