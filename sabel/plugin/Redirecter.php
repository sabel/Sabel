<?php

class Sabel_Plugin_Redirecter extends Sabel_Plugin_Base
{
  public function onRedirect($to)
  {
    if (isset($_SERVER["HTTP_HOST"])) {
      $host = $_SERVER["HTTP_HOST"];
    } else {
      $host = "localhost";
    }
        
    $this->controller->getResponse()->location($host, $to);
  }
}
