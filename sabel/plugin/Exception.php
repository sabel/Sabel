<?php

class Sabel_Plugin_Exception extends Sabel_Plugin_Base
{
  public function onException($exception)
  {
    if (ENVIRONMENT === DEVELOPMENT) {
      if ($exception instanceof Sabel_DB_Exception) {
        $this->controller->exceptionType = "database";
      } else {
        $this->controller->exceptionType = "sabel";
      }
    }
    
    $this->controller->exception = $exception;
  }
}
