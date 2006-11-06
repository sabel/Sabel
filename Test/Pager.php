<?php

require_once('PHPUnit2/Framework/TestCase.php');

/**
 * test case for SabelPager
 *
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Test_Pager extends PHPUnit2_Framework_TestCase
{
  private $pager = null;
  
  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_Pager");
  }
  
  public function setUp()
  {
    $this->pager = Sabel_View_Pager::create();
  }
  
  public function testStandardPagerUse()
  {
    $pager = clone $this->pager;
    
    $pager->setNumberOfItem(100);
    $pager->setLimit(10);
    $pager->setPageNumber(3);
    
    $this->assertEquals(100, $pager->getNumberOfItem());
    $this->assertEquals(10,  $pager->getLimit());
    $this->assertEquals(3,   $pager->getPageNumber());
    $this->assertEquals(10,  $pager->getTotalPageNumber());
    $this->assertEquals(20,  $pager->getSqlOffset());
  }
  
  public function testSetterAndGetterPagerUse()
  {
    $pager = clone $this->pager;
    
    $pager->numberOfItem = 100;
    $pager->limit = 10;
    $pager->pageNumber = 3;
    
    $this->assertEquals(100, $pager->numberOfItem);
    $this->assertEquals(10,  $pager->limit);
    $this->assertEquals(3,   $pager->pageNumber);
    $this->assertEquals(10,  $pager->totalPageNumber);
    $this->assertEquals(20,  $pager->sqlOffset);
  }
  
  public function testPageNumberRoundPagerUse()
  {
    $pager = clone $this->pager;
    
    $pager->setNumberOfItem(300);
    $pager->setLimit(70);
    $pager->setPageNumber(100);
    
    $this->assertEquals(300, $pager->getNumberOfItem());
    $this->assertEquals(70,  $pager->getLimit());
    $this->assertEquals(5,   $pager->getPageNumber());
    $this->assertEquals(5,   $pager->getTotalPageNumber());
    $this->assertEquals(280, $pager->getSqlOffset());
  }
  
  public function testExceptedPagerUse()
  {
    $pager = clone $this->pager;
    
    try {
      $pager->setNumberOfItem(-1);
      $this->fail('set number of item method not thrown.');
    } catch (Sabel_Exception_Runtime $e) {}
    
    try {
      $pager->setNumberOfItem('a');
      $this->fail('set number of item method not thrown.');
    } catch (Sabel_Exception_Runtime $e) {}
    
    $pager->setLimit('a');
    $pager->setPageNumber('a');
    
    $this->assertEquals(0, $pager->getNumberOfItem());
    $this->assertEquals(1, $pager->getLimit());
    $this->assertEquals(1, $pager->getPageNumber());
    $this->assertEquals(1, $pager->getTotalPageNumber());
    $this->assertEquals(0, $pager->getSqlOffset());
  }
  
  public function testUnusualPagerUse()
  {
    $pager = clone $this->pager;
    
    $pager->setPageNumber(10);
    
    $this->assertEquals(1, $pager->getPageNumber());
    $this->assertEquals(1, $pager->getTotalPageNumber());
    $this->assertEquals(0, $pager->getSqlOffset());
    
    $pager->setNumberOfItem(250);
    $pager->setLimit(15);
    
    $this->assertEquals(250, $pager->getNumberOfItem());
    $this->assertEquals(15,  $pager->getLimit());
    $this->assertEquals(10,  $pager->getPageNumber());
    $this->assertEquals(17,  $pager->getTotalPageNumber());
    $this->assertEquals(135, $pager->getSqlOffset());
  }
  
  public function testInitializedPagerUse()
  {
    $pager = Sabel_View_Pager::create(200, 20);
    
    $pager->setPageNumber(4.3);
    
    $this->assertEquals(200, $pager->getNumberOfItem());
    $this->assertEquals(20,  $pager->getLimit());
    $this->assertEquals(4,   $pager->getPageNumber());
    $this->assertEquals(10,   $pager->getTotalPageNumber());
    $this->assertEquals(60,  $pager->getSqlOffset());
  }
}
