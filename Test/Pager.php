<?php

require_once('PHPUnit2/Framework/TestCase.php');

// must need by DI
require_once('sabel/Functions.php');
require_once('sabel/core/Context.php');

uses('sabel.core.Const');
uses('sabel.core.Pager');

/* dummy classes */
class ExtendedSabelPager extends SabelPager
{
  public function __construct()
  {
    $this->initialize();
  }
  
  public function initialize($numberOfItems = null, $numberOfPageItems = null) {
    $this->setPageItem(30);
  }
}

class DummyBasePageController
{
  public function baseInitialize()
  {
    $this->pager = new ExtendedSabelPager();
  }
}

class DummyBbsPageController extends DummyBasePageController
{
  public function __construct()
  {
    $this->baseInitialize();
  }
  
  public function show()
  {
    $bbs = new DummyBBS();
    $this->pager->setNumberOfItems($bbs->count());
    
    return $this->pager->getNumberOfPages();
  }
}

class DummyBBS
{
  public function count()
  {
    return 300;
  }
}
/* dummy classes */


/**
 * test case for SabelPager
 *
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Test_Pager extends PHPUnit2_Framework_TestCase
{
  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_Pager");
  }
  
  public function testStandardPagerUse()
  {
    $numberOfItems = 300;
    $numberOfPageItems = 5;
    $pager = new SabelPager();
    $pager->initialize($numberOfItems, $numberOfPageItems);
    $this->assertEquals(($numberOfItems / $numberOfPageItems), $pager->getNumberOfPages());
  }
  
  public function testExtendedPagerUse()
  {
    $pager = new ExtendedSabelPager();
    $count = 400;
    $pager->setNumberOfItems($count);
    $this->assertEquals(((int)ceil($count / 30)), $pager->getNumberOfPages());
  }
  
  public function testControllerSimuration()
  {
    $controller = new DummyBbsPageController();
    $this->assertEquals((300 / 30), $controller->show());
  }
  
  public function testExceptedUse()
  {
    $pager = new SabelPager(1);
    
    try {
      // this will throw exception
      $pager->getNumberOfPage();
    } catch (Exception $e) {
    }
  }
}