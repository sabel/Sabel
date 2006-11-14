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
  
  public function testStandardUse()
  {
    $pager = Sabel_View_Pager::create(200, 10);
    $pager->pageNumber = 10;
    $pv = new Sabel_View_PageViewer($pager);
    
    $this->assertTrue($pv->isCurrent());
    $this->assertFalse($pv->isFirst());
    
    $this->assertEquals(10, $pv->getCurrent());
    $this->assertEquals(11, $pv->getNext());
    $this->assertEquals(9, $pv->getPrevious());
    
    $num = 5;
    foreach ($pv as $page) $this->assertEquals($num++, $page->getCurrent());
    $this->assertEquals(15, $num);
    $this->assertEquals(10, $pv->getCurrent());
    $this->assertEquals(11, $pv->getNext());
    $this->assertEquals(9, $pv->getPrevious());
    
    $this->assertEquals(5, $pv->getPage(5));
    
    $this->assertEquals(20, $pv->getLast());
    $this->assertEquals(1,  $pv->getFirst());
  }
  
  public function testStandardUseAndPageNumberFirst()
  {
    $pager = Sabel_View_Pager::create(200, 10);
    $pager->pageNumber = 1;
    $pv = new Sabel_View_PageViewer($pager);
    
    $this->assertTrue($pv->isFirst());
    $this->assertTrue($pv->isCurrent());
    $this->assertFalse($pv->isLast());
    
    $this->assertEquals(1, $pv->getCurrent());
    $this->assertEquals(2, $pv->getNext());
    $this->assertEquals(1, $pv->getPrevious());
    
    $num = 1;
    foreach ($pv as $page) $this->assertEquals($num++, $page->getCurrent());
    $this->assertEquals(6, $num);
    $this->assertEquals(1, $pv->getCurrent());
    $this->assertEquals(2, $pv->getNext());
    $this->assertEquals(1, $pv->getPrevious());
  }
  
  public function testStandardUseAndPageNumberLast()
  {
    $pager = Sabel_View_Pager::create(200, 10);
    $pager->pageNumber = 20;
    $pv = new Sabel_View_PageViewer($pager);
    
    $this->assertFalse($pv->isFirst());
    $this->assertTrue($pv->isCurrent());
    $this->assertTrue($pv->isLast());
    
    $this->assertEquals(20, $pv->getCurrent());
    $this->assertEquals(20, $pv->getNext());
    $this->assertEquals(19, $pv->getPrevious());
    
    $num = 15;
    foreach ($pv as $page) $this->assertEquals($num++, $page->getCurrent());
    $this->assertEquals(21, $num);
    $this->assertEquals(20, $pv->getCurrent());
    $this->assertEquals(20, $pv->getNext());
    $this->assertEquals(19, $pv->getPrevious());
  }
  
  public function testStandardUseAndWindowSizeEdit()
  {
    $pager = Sabel_View_Pager::create(200, 10);
    $pager->pageNumber = 3;
    $pv = new Sabel_View_PageViewer($pager);
    $pv->setWindow(7);
    
    $this->assertFalse($pv->isFirst());
    $this->assertFalse($pv->isLast());
    
    $this->assertEquals(3, $pv->getCurrent());
    
    $num = 1;
    foreach ($pv as $page) $this->assertEquals($num++, $page->getCurrent());
    $this->assertEquals(7, $num);
  }
  
  public function testStandardUseAndPriorityNext()
  {
    $pager = Sabel_View_Pager::create(280, 20);
    $pager->pageNumber = 12;
    $pv = new Sabel_View_PageViewer($pager);
    $pv->setPriorityNext();
    $pv->setWindow(8);
    
    $num = 9;
    foreach ($pv as $page) $this->assertEquals($num++, $page->getCurrent());
    $this->assertEquals(15, $num);
  }
  
  public function testStandardUseAndStartPageIgnore()
  {
    $pager = Sabel_View_Pager::create(300, 25);
    $pager->pageNumber = 2;
    $pv = new Sabel_View_PageViewer($pager);
    $pv->setIgnoreEmpty(false);
    $pv->setWindow(9);
    
    $num = 1;
    foreach ($pv as $page) $this->assertEquals($num++, $page->getCurrent());
    $this->assertEquals(10, $num);
  }
  
  public function testStanderdUseAndEndPageIgnore()
  {
    $pager = Sabel_View_Pager::create(300, 25);
    $pager->pageNumber = 10;
    $pv = new Sabel_View_PageViewer($pager);
    $pv->setIgnoreEmpty(false);
    $pv->setWindow(9);
    
    $num = 4;
    foreach ($pv as $page) $this->assertEquals($num++, $page->getCurrent());
    $this->assertEquals(13, $num);
  }
}
