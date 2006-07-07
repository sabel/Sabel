<?php

require_once('PHPUnit2/Framework/TestCase.php');

// must need by DI
require_once('sabel/Functions.php');
require_once('sabel/core/Context.php');

uses('sabel.exception.Runtime');

uses('sabel.container.DI');
uses('sabel.injection.Calls');
uses('sabel.core.Exception');
uses('sabel.core.Const');
uses('sabel.request.Parameters');
uses('sabel.request.ParsedRequest');

class Test_ParsedRequest extends PHPUnit2_Framework_TestCase
{
  public $pp = null;
  
  protected function setUp()
  {
    $this->pp = ParsedRequest::create();
  }
  
  protected function tearDown()
  {
    $this->pp->destruct();
  }
  
  public function testFlexibleURI()
  {
    $uri  = '2006/07/05';
    $pair = 'year/month/day';
    $pat  = array('([1-2][0-9]{3})', '([0-1]?[0-9])', '([0-3]?[0-9])');
    
    $pp = $this->pp->parse($uri, $pair, $pat);
    $this->assertEquals('2006', $pp->year);
    $this->assertEquals('07',   $pp->month);
    $this->assertEquals('05',   $pp->day);
    $this->assertNull(  $pp->parameters);
  }
  
  public function testFlexibleURI_with_param()
  {
    $uri = '2006/07/05?parameter&key=value';
    $pair = 'year/month/day';
    $pat  = array('([1-2][0-9]{3})', '([0-1]?[0-9])', '([0-3]?[0-9])');
    
    $pp = $this->pp->parse($uri, $pair, $pat);
    $this->assertEquals('2006', $pp->year);
    $this->assertEquals('07',   $pp->month);
    $this->assertEquals('05',   $pp->day);
    
    $this->assertEquals('parameter&key=value', $pp->parameters);
  }
  
  public function testFlexibleURI_shortcut()
  {
    $uri  = '2006/07';
    $pair = 'year/month/day';
    $pat  = array('([1-2][0-9]{3})', '([0-1]?[0-9])', '([0-3]?[0-9])');
    
    $pp = $this->pp->parse($uri, $pair, $pat);
    $this->assertEquals('2006', $pp->year);
    $this->assertEquals('07',   $pp->month);
    $this->assertNull($pp->day);
  }
  
  public function testFlexibleURI_shortcut_with_param()
  {
    $uri = '2006/07/?parameter&key=value';
    $pair = 'year/month/day';
    $pat  = array('([1-2][0-9]{3})', '([0-1]?[0-9])', '([0-3]?[0-9])');
    
    $pp = $this->pp->parse($uri, $pair, $pat);
    $this->assertEquals('2006', $pp->year);
    $this->assertEquals('07',   $pp->month);
    $this->assertNull($pp->day);
    
    $this->assertEquals('parameter&key=value', $pp->parameters);
  }
  
  public function testFlexibleURI_null()
  {
    $uri  = '';
    $pair = 'year/month/day';
    $pat  = array('([1-2][0-9]{3})', '([0-1]?[0-9])', '([0-3]?[0-9])');
    
    $pp = $this->pp->parse($uri, $pair, $pat);
    $this->assertNull($pp->year);
    $this->assertNull($pp->month);
    $this->assertNull($pp->day);
    
    $this->assertNull($pp->parameters);
  }
  
  public function testFlexibleURI_ignore_eleven()
  {
    $uri = '2006/07/05/11?parameter';
    $pair = 'year/month/day';
    $pat  = array('([1-2][0-9]{3})', '([0-1]?[0-9])', '([0-3]?[0-9])');
    
    $pp = $this->pp->parse($uri, $pair, $pat);
    $this->assertEquals('2006', $pp->year);
    $this->assertEquals('07',   $pp->month);
    $this->assertEquals('05',   $pp->day);
    
    $this->assertEquals('parameter', $pp->parameters);
  }
  
  public function testDefault()
  {
    $uri = 'module/controller/action?parameter';
    
    $pp = $this->pp->parse($uri);
    $this->assertEquals('module',     $pp->module);
    $this->assertEquals('controller', $pp->controller);
    $this->assertEquals('action',     $pp->action);
    $this->assertEquals('parameter',  $pp->parameters);
  }
  
  public function testComplexURI()
  {
    $uri  = 'hamanaka/archive/2006/05/05';
    $pair = 'user/type/year/month';
    $pat  = array();

    $pp = $this->pp->parse($uri, $pair, $pat);
    $this->assertEquals('hamanaka', $pp->user);
    $this->assertEquals('archive',  $pp->type);
    $this->assertEquals('2006',     $pp->year);
    $this->assertEquals('05',       $pp->month);
    $this->assertEquals(null,       $pp->day);
  }
}

?>