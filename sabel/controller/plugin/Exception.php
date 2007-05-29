<?php

class Sabel_Controller_Plugin_Exception extends Sabel_Controller_Page_Plugin
{
  public function onException($exception)
  {
    echo "<PRE>";
    echo $exception->getTraceAsString();
    echo $exception->getMessage();
  }
}
