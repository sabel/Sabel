<?php

require_once('PHPUnit2/Framework/TestCase.php');

/**
 * test case for SabelPager
 *
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Test_PageViewer extends PHPUnit2_Framework_TestCase
{
  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_PageViewer");
  }
  
  public function setUp()
  {
  }
  
  public function testPageViewer() // 挙動
  {
    $pager = Sabel_View_Pager::create(200, 10);
    $pager->pageNumber = 1;
    $pv = new Sabel_View_PageViewer($pager);
    
    for ($i = 2; $i < 10; $i++) $this->assertEquals($i, $pv->getNext());
    for ($i = 8; $i > 1; $i--)  $this->assertEquals($i, $pv->getPrevious());
  }
}
