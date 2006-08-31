<?php

require_once('PHPUnit2/Framework/TestCase.php');

class Test_Sabel extends PHPUnit2_Framework_TestCase
{
  public function testSabelWeb()
  {
    $fc = new Sabel_Controller_Front();
  }
}