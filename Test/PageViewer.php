<?php

require_once('PHPUnit2/Framework/TestCase.php');

/**
 * test case for SabelPager
 *
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Test_PageViewer extends PHPUnit2_Framework_TestCase
{
  private $pv = null;
  
  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_PageViewer");
  }
  
  public function setUp()
  {
    $pager = Sabel_View_Pager::create();
    $this->pv = new Sabel_View_PageViewer($pager);
  }
  
  public function testInitializedPagerUse()
  {
    $pv = clone $this->pv;
  }
}
