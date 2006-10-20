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
  
  public function __get($key)
  {
    return $this->get($key);
  }
  
  public function get($key)
  {
    $pp = $this->parsedParameters;
    return (isset($pp[$key])) ? $pp[$key] : null;
  }
  
  /**
   * Parsing URL request
   *
   * @param array $parameter list of URI parts
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
}