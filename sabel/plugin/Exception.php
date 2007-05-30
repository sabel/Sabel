<?php

class Sabel_Plugin_Exception extends Sabel_Plugin_Base
{
  public function onException($exception)
  {
    echo "<PRE>";
    echo $exception->getTraceAsString();
    echo $exception->getMessage();
  }
}
