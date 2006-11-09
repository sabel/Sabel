<?php

class Test_Namespace extends PHPUnit2_Framework_TestCase
{
  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_Namespace");
  }
  
  public function __construct()
  {
    
  }
  
  public function setUp()
  {
    $this->c = Container::create();
  }
  
  public function tearDown()
  {
    
  }
  
  public function testNamespace()
  {
    $root = new Sabel_Core_Namespace();
    $core = new Sabel_Core_Namespace('core');
    $root->addNamespace($core);
    $core->addClass('Foo');
    unset($core);
    $core = $root->getNamespace('core');
    $this->assertEquals('core', $core->getName());
    $className = $core->getClassName('Foo');
    $this->assertEquals('Core_Foo', $className);
    // $className = $root->getClassName('core.Foo');
    //$this->assertEquals('Core_Foo', $className);
  }
  
  public function testRealisticNamespace()
  {
    $rootroot = new Sabel_Core_Namespace();
    $root = new Sabel_Core_Namespace('root');
    $core = new Sabel_Core_Namespace('core');
    $child = new Sabel_Core_Namespace('child');
    $core->addNamespace($child);
    $child->addClass('Foo');
    $root->addNamespace($core);
    $rootroot->addNamespace($root);
    unset($core);
    $core = $rootroot->getNamespace('root.core');
    $this->assertEquals('core', $core->getName());
    $className = $core->getClassName('child.Foo');
    $this->assertEquals('Root_Core_Child_Foo', $className);
  }
  
  public function testSoMuchNSHierarcy()
  {
    $root    = new Sabel_Core_Namespace();
    $sabel = new Sabel_Core_Namespace('sabel',   $root);
     $core = new Sabel_Core_Namespace('core',    $sabel);
      $ctlr = new Sabel_Core_Namespace('controller', $core);
     $db  = new Sabel_Core_Namespace('db',      $sabel);
      $driver  = new Sabel_Core_Namespace('driver',  $db);
       $schema  = new Sabel_Core_Namespace('schema',  $driver);
        $table   = new Sabel_Core_Namespace('table',   $schema);
         $column  = new Sabel_Core_Namespace('column',  $table);
          $type    = new Sabel_Core_Namespace('type',    $column);
           $address = new Sabel_Core_Namespace('address', $type);
            $bit     = new Sabel_Core_Namespace('bit',     $address);
            $bit->addClass('Zero');
            $bit->addClass('One');
    
    $this->assertEquals('Sabel_Core_Controller_Page', $core->getClassName('controller.Page'));
    $className = 'Sabel_Db_Driver_Schema_Table_Column_Type_Address_Bit_Zero';
    $this->assertEquals($className, $column->getClassName('type.address.bit.Zero'));
    $this->assertEquals($className, $type->getClassName('address.bit.Zero'));
    $this->assertEquals($className, $address->getClassName('bit.Zero'));
    $this->assertEquals($className, $bit->getClassName('Zero'));
    $this->assertEquals($className, $bit->getClassName('sabel.db.driver.schema.table.column.type.address.bit.Zero'));
  }
    
  
  public function testAutoSoMuchNSHierarcy()
  {
    $str = new String('a');
    $root = new Sabel_Core_Namespace();
    $c = new Sabel_Core_Namespace('a');
    
    $a = $c;
    $cn = array('A');
    $hierarcy = 15;
    for ($i = 0; $i < $hierarcy; $i++) {
      $inc = $str->succ();
      $tns = new Sabel_Core_Namespace($inc);
      $tns->addNamespace($c);
      $c = $tns;
      $cn[] = strtoupper($inc);
    }
    $a->addClass('Alpha');
    
    $st = array();
    $a->getParentName($st);
    $className = $a->getClassName('Alpha');
    $expectedClassName = join('_', array_reverse($cn)).'_Alpha';
    $this->assertEquals($expectedClassName, $className);
  }
  
  public function estAnotherClassName()
  {
    $root = new Sabel_Core_Namespace();
    $core = new Sabel_Core_Namespace('core');
    $core->addClass('Foo');
    $root->addNamespace($core);
    unset($core);
    $core = $root->getNamespace('core');
    $className = $root->getClassName('core.Foo');
    $this->assertEquals('Foo', $className);
  }
}