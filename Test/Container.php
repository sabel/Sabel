<?php

/**
 * Test_Container
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Test_Container extends SabelTestCase
{
  public static function suite()
  {
   return new PHPUnit2_Framework_TestSuite("Test_Container");
  }
  
  public function testDummy()
  {
    $this->assertTrue(true);
  }
}