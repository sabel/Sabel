<?php

interface Sabel_Controller_Page_Plugin
{
  public function onBeforeAction($controller);
  public function onAfterAction($controller);
  public function onRedirect($controller);
  public function onException($controller, $exception);
}