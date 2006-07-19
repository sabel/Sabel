<?php

require_once('PHPUnit2/Framework/TestCase.php');

function a($key, $val)
{
  return array($key, $val);
}

class Test_PHP extends PHPUnit2_Framework_TestCase
{
  public function estArrayPerformance()
  {
    for ($i=0; $i < 10000; $i++) {
      $array = a('test', 'test');
    }
  }
  
  public function testArrayPerformance2()
  {
    for ($i=0; $i < 10000; $i++) {
      $array = array('test' => 'test');
    }
  }
  
  public function testArrayTest()
  {
    $a['test'] = 'test';
  }
}

?>