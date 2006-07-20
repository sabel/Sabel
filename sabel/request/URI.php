<?php

/**
 * Sabel_Request_URI
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Request_URI
{
  public function __construct()
  {
    
  }
  
  public static function getUri()
  {
    if ($_SERVER['argv']{0} == './sabel.php' || $_SERVER['argv']{0} == 'sabel.php') {
      $args = $_SERVER['argv'];
      array_shift($args);
      $request_uri = join('/', $args);
    } else {
      $request_uri = ltrim($_SERVER['REQUEST_URI'], '/');
    }
    
    return $request_uri;
  }
}