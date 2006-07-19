<?php

require_once('PHPUnit2/Framework/TestCase.php');

// must need by DI
require_once('sabel/Functions.php');
require_once('sabel/core/Context.php');

Sabel_Core_Context::addIncludePath('');
uses('sabel.container.DI');
uses('sabel.injection.Calls');
uses('sabel.core.Exception');
uses('sabel.core.Const');
uses('sabel.request.Parameters');
uses('sabel.request.ParsedRequest');

uses('sabel.controller.Front');

class Test_Sabel extends PHPUnit2_Framework_TestCase
{
  public function testSabelWeb()
  {
    $fc = new Sabel_Controller_Front();
  }
}