<?php

require_once('PHPUnit2/Framework/TestCase.php');

/**
 * test case for SabelPager
 *
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Test_TagPageViewer extends PHPUnit2_Framework_TestCase
{
  protected $tag = '';
  
  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_TagPageViewer");
  }
  
  public function setUp()
  {
    $this->tag = '<a href="' . uri('', false) . '">%d</a>';
  }
  
  public function testStandardUse()
  {
    $pager = Sabel_View_Pager::create(200, 10);
    $pager->pageNumber = 10;
    $pv = new Sabel_View_TagPageViewer($pager);
    
    $this->assertEquals('<a href="index/index">10</a>', $pv->getCurrent());
    $this->assertEquals('<a href="index/index">11</a>', $pv->getNext());
    $this->assertEquals('<a href="index/index">9</a>',  $pv->getPrevious());
    
    $this->assertEquals('<a href="index/index">20</a>', $pv->getLast());
    $this->assertEquals('<a href="index/index">1</a>',  $pv->getFirst());
    $this->assertEquals('<a href="index/index">8</a>', $pv->getPage(8));
  }
}
