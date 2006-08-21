<?php

require_once('PHPUnit2/Framework/TestCase.php');

// must need by DI
require_once('sabel/Functions.php');
require_once('sabel/core/Context.php');

require_once('sabel/core/Resolver.php');

/**
 * test case for SabelPager
 *
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Test_Resolver extends PHPUnit2_Framework_TestCase
{
  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_Resolver");
  }
  
  public function testResolver()
  {
    $classpath = 'root.dir.dir2.Class';
    
    $className = Sabel_Core_Resolver::resolvClassName($classpath);
    $path      = Sabel_Core_Resolver::resolvPath($classpath);
    $classpath = Sabel_Core_Resolver::resolvClassPathByClassName('Root_Dir_Dir2_Class');
    
    $this->assertEquals('Root_Dir_Dir2_Class', $className);
    $this->assertEquals('root/dir/dir2/Class', $path);
    $this->assertEquals('root.dir.dir2.Class', $classpath);
  }
}