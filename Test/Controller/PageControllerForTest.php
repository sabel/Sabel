<?php

class PageControllerForTest extends Sabel_Controller_Page
{
  public function testAction()
  {
    return array("test" => "test");
  }
}