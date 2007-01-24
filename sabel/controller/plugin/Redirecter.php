<?php

class Sabel_Controller_Plugin_Redirecter implements Sabel_Controller_Page_Plugin
{
  protected static $listener = array();
  
  public static function setListener($listener)
  {
    self::$listener = $listener;
  }
  
  public function onException($controller, $exception) {}
  public function onBeforeAction($controller) {}
  public function onAfterAction($controller) {}
  
  public function onRedirect($controller, $to = null)
  {
    if (isset($_SERVER['HTTP_HOST'])) {
      $host = $_SERVER['HTTP_HOST'];
    } else {
      $host = "localhost";
    }
    $absolute = 'http://' . $host;
    $redirect = 'Location: ' . $absolute . '/' . $to;
    
    if (defined("FUNCTIONAL_TEST")) {
      self::$listener->notify($to);
    } else {
      header ($redirect);
    }
  }
}