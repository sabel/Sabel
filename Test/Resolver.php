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
  public function testResolver()
  {
    $r = new Sabel_Core_Resolver('root.dir.dir.Class');
    $path = $r->resolvClassName();
    print_r($path);
  }
}