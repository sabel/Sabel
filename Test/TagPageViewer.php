<?php

require_once('PHPUnit2/Framework/TestCase.php');

/**
 * test case for SabelPager
 *
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Test_TagPageViewer extends PHPUnit2_Framework_TestCase
{
  protected $facade = null;
  
  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_TagPageViewer");
  }
  
  public function setUp()
  {
    if (is_null($this->facade)) {
      $this->facade = $this->createFacade();
      $this->tag    = '<a href="' . uri('', false) . '">%d</a>';
    }
  }
  
  public function testStandardUse()
  {
    $pager = Sabel_View_Pager::create(200, 10);
    $pager->pageNumber = 10;
    $pv = new Sabel_View_TagPageViewer($pager);
    
    $this->assertTrue($pv->isCurrent());
    $this->assertFalse($pv->isFirst());
    
    $this->assertEquals(sprintf($this->tag, 10), $pv->getCurrent());
    $this->assertEquals(sprintf($this->tag, 11), $pv->getNext());
    $this->assertEquals(sprintf($this->tag, 9),  $pv->getPrevious());
    
    $num = 5;
    foreach ($pv as $page)
      $this->assertEquals(sprintf($this->tag, $num++), $page->getCurrent());
    $this->assertEquals(15, $num);
    $this->assertEquals(sprintf($this->tag, 10), $pv->getCurrent());
    $this->assertEquals(sprintf($this->tag, 11), $pv->getNext());
    $this->assertEquals(sprintf($this->tag, 9),  $pv->getPrevious());
    
    $this->assertEquals(sprintf($this->tag, 5), $pv->getPage(5));
    
    $this->assertEquals(sprintf($this->tag, 20), $pv->getLast());
    $this->assertEquals(sprintf($this->tag, 1),  $pv->getFirst());
  }
  
  protected function createFacade()
  {
    $facade = Sabel_Map_Facade::create();
    $facade->setRequestUri(new SabeL_Request_Request());
    
    $entry = new Sabel_Map_Entry('photo');
    $entry->setUri(new Sabel_Map_Uri('photo/show/list'));
    
    $facade->setEntry('photo', $entry);
    $facade->getEntry('photo');
    
    return $facade;
  }
}
