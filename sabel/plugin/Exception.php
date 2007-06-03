<?php

class Sabel_Plugin_Exception extends Sabel_Plugin_Base
{
  public function enable()
  {
    return array(parent::ON_EXCEPTION);
  }

  public function onException($exception)
  {
    if (ENVIRONMENT === DEVELOPMENT) {
      echo "<PRE>";
      print_r($exception->getTraceAsString());
      print_r($exception->getMessage());
      echo "</PRE>";
    }
  }
}
