<?php

/**
 * Sabel_Request_Parameters
 * 
 * @package org.sabel.request
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Request_Parameters
{
  protected $parameters = '';
  protected $parsedParameters = array();
  
  public function __construct($parameters)
  {
    $this->parameters = $parameters;
    if (!empty($parameters)) $this->parse();
  }
  
  /**
   * Parsing URL request
   *
   * @param void
   * @return void
   */
  protected function parse()
  {
    $separate = explode("&", $this->parameters);
    $sets = array();
    foreach ($separate as $val) {
      $tmp = explode("=", $val);
      if (is_null($tmp[1])) $tmp[1] = '';
      $enc = mb_detect_encoding($tmp[1], 'UTF-8, EUC_JP, SJIS');
      $sets[$tmp[0]] = mb_convert_encoding(urldecode($tmp[1]), 'EUC_JP', $enc);
    }
    
    $this->parsedParameters =& $sets;
  }
  
  public function __get($key)
  {
    return $this->get($key);
  }
  
  public function get($key)
  {
    $pp = $this->parsedParameters;
    return (isset($pp[$key])) ? $pp[$key] : null;
  }
}