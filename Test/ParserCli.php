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
uses('sabel.request.parser.Cli');

class Test_ParserCli extends PHPUnit2_Framework_TestCase
{
  public $pr = null;
  
  protected function setUp()
  {
    $this->pr = Sabel_Request_Parser_Cli::create();
  }
  
  protected function tearDown()
  {
    $this->pr->destruct();
  }
  
  public function testFlexibleURI()
  {
    $uri  = array('2006', '07', '05');
    $pair = 'year/month/day';
    $pat  = array('([1-2][0-9]{3})', '([0-1]?[0-9])', '([0-3]?[0-9])');
    
    $pr = $this->pr->parse($uri, $pair, $pat);
    $this->assertEquals('2006', $pr->year);
    $this->assertEquals('07',   $pr->month);
    $this->assertEquals('05',   $pr->day);
    $this->assertNull($pr->parameters);
  }
  
  public function testFlexibleURI_with_param()
  {
    $uri  = array('2006', '07', '05', 'parameter&key=value');
    $pair = 'year/month/day';
    $pat  = array('([1-2][0-9]{3})', '([0-1]?[0-9])', '([0-3]?[0-9])');
    
    $pr = $this->pr->parse($uri, $pair, $pat);
    $this->assertEquals('2006', $pr->year);
    $this->assertEquals('07',   $pr->month);
    $this->assertEquals('05',   $pr->day);
    
    $this->assertEquals('parameter&key=value', $pr->parameters);
  }
  
  public function testFlexibleURI_shortcut()
  {
    $uri  = '2006/07';
    $pair = 'year/month/day';
    $pat  = array('([1-2][0-9]{3})', '([0-1]?[0-9])', '([0-3]?[0-9])');
    
    $pr = $this->pr->parse($uri, $pair, $pat);
    $this->assertEquals('2006', $pr->year);
    $this->assertEquals('07',   $pr->month);
    $this->assertNull($pr->day);
  }
  
  public function testFlexibleURI_shortcut_with_param()
  {
    $uri = '2006/07/?parameter&key=value';
    $pair = 'year/month/day';
    $pat  = array('([1-2][0-9]{3})', '([0-1]?[0-9])', '([0-3]?[0-9])');
    
    $pr = $this->pr->parse($uri, $pair, $pat);
    $this->assertEquals('2006', $pr->year);
    $this->assertEquals('07',   $pr->month);
    $this->assertNull($pr->day);
    
    $this->assertEquals('parameter&key=value', $pr->parameters);
  }
  
  public function testFlexibleURI_null()
  {
    $uri  = '';
    $pair = 'year/month/day';
    $pat  = array('([1-2][0-9]{3})', '([0-1]?[0-9])', '([0-3]?[0-9])');
    
    $pr = $this->pr->parse($uri, $pair, $pat);
    $this->assertNull($pr->year);
    $this->assertNull($pr->month);
    $this->assertNull($pr->day);
    
    $this->assertNull($pr->parameters);
  }
  
  public function testFlexibleURI_ignore_eleven()
  {
    $uri = '2006/07/05/11?parameter';
    $pair = 'year/month/day';
    $pat  = array('([1-2][0-9]{3})', '([0-1]?[0-9])', '([0-3]?[0-9])');
    
    $pr = $this->pr->parse($uri, $pair, $pat);
    $this->assertEquals('2006', $pr->year);
    $this->assertEquals('07',   $pr->month);
    $this->assertEquals('05',   $pr->day);
    
    $this->assertEquals('parameter', $pr->parameters);
  }
  
  public function testDefault()
  {
    $uri = 'module/controller/action?parameter';
    
    $pr = $this->pr->parse($uri);
    $this->assertEquals('module',     $pr->module);
    $this->assertEquals('controller', $pr->controller);
    $this->assertEquals('action',     $pr->action);
    $this->assertEquals('parameter',  $pr->parameters);
  }
  
  public function testComplexURI()
  {
    $uri  = 'hamanaka/archive/2006/05/05';
    $pair = 'user/type/year/month';
    $pat  = array();

    $pr = $this->pr->parse($uri, $pair, $pat);
    $this->assertEquals('hamanaka', $pr->user);
    $this->assertEquals('archive',  $pr->type);
    $this->assertEquals('2006',     $pr->year);
    $this->assertEquals('05',       $pr->month);
    $this->assertEquals(null,       $pr->day);
  }
}

?>