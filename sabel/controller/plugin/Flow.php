<?php

class Sabel_Controller_Plugin_Flow extends Sabel_Controller_Page_Plugin
{
  public function flow()
  {
    $storage = $this->controller->getStorage();
    return $storage->read("flow");
  }
}
