<?php

/**
 * 
 *
 */
class Sabel_Request_Parameters
{
  protected $parameters;
  protected $parsedParameters;
  
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
    foreach ($separate as $key => $val) {
      $tmp = explode("=", $val);
      if (empty($tmp[1])) $tmp[1] = '';
      $enc   = mb_detect_encoding($tmp[1], 'UTF-8, EUC_JP, SJIS');
      $sets[$tmp[0]] = mb_convert_encoding(urldecode($tmp[1]), 'EUC-JP', $enc);
    }
    
    $this->parsedParameters =& $sets;
  }
  
  public function __get($key)
  {
    return $this->parsedParameters[$key];
  }
  
  public function get($key)
  {
    return $this->parsedParameters[$key];
  }
}