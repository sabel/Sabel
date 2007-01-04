<?php

Sabel::fileUsing('sabel/String.php');

class Test_Namespace extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Namespace");
  }
  
  public function __construct()
  {
    
  }
  
  public function setUp()
  {
  }
  
  public function tearDown()
  {
    
  }
  
  public function testRealisticNamespaceUseConstructor()
  {
    $root  = new Sabel_Core_Namespace();
    $sabel = new Sabel_Core_Namespace('sabel', $root);
    $core  = new Sabel_Core_Namespace('core',  $sabel);
    $view  = new Sabel_Core_Namespace('view',  $sabel);
    $child = new Sabel_Core_Namespace('child', $core);
    
    $child->addClass('Foo');
    $child->addClass('Bar');
    $view->addClass('Foo');
    $view->addClass('Bar');
    
    $core = $root->getNamespace('sabel.core');
    $this->assertEquals('Sabel_Core_Child_Foo', $core->getClassName('child.Foo'));
    $this->assertEquals('Sabel_Core_Child_Bar', $core->getClassName('child.Bar'));
    
    $sabel = $root->getNamespace('sabel');
    $view  = $sabel->getNamespace('view');
    $this->assertEquals('Sabel_View_Foo', $view->getClassName('Foo'));
    $this->assertEquals('Sabel_View_Bar', $view->getClassName('Bar'));
  }
  
  public function testRealisticNamespaceUseAddNamespace()
  {
    $root  = new Sabel_Core_Namespace();
    $sabel = new Sabel_Core_Namespace('sabel');
    $core  = new Sabel_Core_Namespace('core');
    $view  = new Sabel_Core_Namespace('view');
    $child = new Sabel_Core_Namespace('child');
    
    $core->addNamespace($child);
    $sabel->addNamespace($view);
    $sabel->addNamespace($core);
    $root->addNamespace($sabel);
    
    $core->addClass('Foo');
    $child->addClass('Foo');
    $child->addClass('Bar');
    
    $core = $root->getNamespace('sabel.core');
    $this->assertEquals('Sabel_Core_Foo',       $core->getClassName('Foo'));
    $this->assertEquals('Sabel_Core_Child_Foo', $core->getClassName('child.Foo'));
    $this->assertEquals('Sabel_Core_Child_Bar', $core->getClassName('child.Bar'));
    
    $this->assertEquals(array('Foo', 'Bar'), $child->getClasses());
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
    $this->assertEquals($className, $bit->getClassName(
      'sabel.db.driver.schema.table.column.type.address.bit.Zero'
    ));
  }
  
  public function testAutoSoMuchNSHierarcy()
  {
    $str = new String('a');
    $root = new Sabel_Core_Namespace();
    $c = new Sabel_Core_Namespace('a');
    
    $a = $c;
    $cn = array('A');
    $hierarcy = 25;
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
}
