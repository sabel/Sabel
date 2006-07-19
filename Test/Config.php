<?php

require_once('PHPUnit2/Framework/TestCase.php');

// must need by DI
require_once('sabel/Functions.php');
require_once('sabel/core/Context.php');

require_once('sabel/config/Spyc.php');
require_once('sabel/config/Yaml.php');

/**
 * test case for SabelPager
 *
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Test_Config extends PHPUnit2_Framework_TestCase
{
  public function testYaml()
  {
    $c = new Sabel_Config_Yaml('Test/data/map.yml');
    $blog = $c->get('blog');
  }
}