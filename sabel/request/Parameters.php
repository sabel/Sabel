<?php

/**
 * Sabel_Request_Parameters class
 * 
 * @package org.sabel.request
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Request_Parameters
{
  protected $parsedParameters = array();
  
  public function __construct($parameters)
  {
    $parameters = str_replace('?', '', $parameters);
    if (!empty($parameters)) $this->parse($parameters);
  }
  
  /**
   * Parsing URL request
   *
   * @param void
   * @return void
   */
  protected function parse($parameters)
  {
    $parameters = explode("&", $parameters);
    $sets = array();
    foreach ($parameters as $piar) {
      @list($key, $val) = explode('=', $piar);
      $sets[$key] = $val;
    }
    $this->parsedParameters = $sets;
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
