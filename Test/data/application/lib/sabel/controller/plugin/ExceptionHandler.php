<?php

class Sabel_Controller_Plugin_ExceptionHandler extends Sabel_Plugin_Base
{
  public function onException($controller, $exception)
  {
    $c = new Common_ExceptionHandler();
    $c->setup($controller->getRequest());
    $c->initialize();
    $ref = new ReflectionClass($exception);
    $className = str_replace("Exception_", "", $ref->getName());
    if ($ref->hasMethod($className)) {
      $c->$className($exception);
    } else {
      $c->exception($exception);
    }
  }
}