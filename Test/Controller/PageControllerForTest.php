<?php

class PageControllerForTest extends Sabel_Controller_Page
{
  public function testAction()
  {
    return array("test" => "test");
  }
  
  public function testActionWithParameter()
  {
    // __get($name)
    // getParameters()
    // hasParameter($name)
    // getParameter($name)
    return array("test" => $this->test);
  }
}