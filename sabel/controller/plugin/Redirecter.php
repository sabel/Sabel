<?php

class Sabel_Controller_Plugin_Redirecter extends Sabel_Controller_Page_Plugin
{
  public function onRedirect($controller, $to = null)
  {
    if (!isset($_SERVER["HTTP_HOST"])) {
      throw new Sabel_Exception_Runtime('$_SERVER["HTTP_HOST"] not found');
    }
    
    $host = $_SERVER["HTTP_HOST"];
    
    $absolute = 'http://' . $host;
    $redirect = 'Location: ' . $absolute . '/' . $to;
    
    header ($redirect);
    exit;
  }
}