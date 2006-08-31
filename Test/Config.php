<?php

require_once('PHPUnit2/Framework/TestCase.php');

/**
 * test case for Sabel_Config_*
 *
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Test_Config extends PHPUnit2_Framework_TestCase
{
  public function testLoad()
  {
    $c = new Sabel_Config_Yaml('Test/data/map.yml');
    $blog = $c->read('blog');
  }
  
  public function testLoadFileNotFound()
  {
    $c = new Sabel_Config_Yaml('Test/data/dummy.yml');
    $dummy = $c->read('test');
    $this->assertFalse($c->isValid());
  }
}