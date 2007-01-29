<?php

class PageControllerForTest extends Sabel_Controller_Page
{
  public function testAction()
  {
    return array("test" => "test");
  }
  
  public function testActionWithParameter()
  {
    return array("test" => $this->test);
  }
  
  public function testVolatile()
  {
    $this->volatile("test", "test");
  }
}