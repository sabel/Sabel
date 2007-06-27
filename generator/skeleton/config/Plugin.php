<?php

class Plugin extends Sabel_Plugin_Config
{
  public function configure()
  {
    $this->add(new Sabel_Plugin_Common());
    $this->add(new Sabel_Plugin_View());
    $this->add(new Sabel_Plugin_Redirecter());
    $this->add(new Sabel_Plugin_Exception());
    $this->add(new Sabel_Plugin_Errors());
    // $this->add(new Sabel_Plugin_Acl());
  }
}
