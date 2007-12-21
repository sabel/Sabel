<?php

class Admin_Controllers_Index extends Sabel_Controller_Page
{
  public function initialize()
  {
    $this->redirect->to("c: main, a: index");
  }
}
