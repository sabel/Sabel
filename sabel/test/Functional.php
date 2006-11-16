<?php

if (defined('TEST_CASE')){

if (!defined('PHPUnit2_MAIN_METHOD'))
  define('PHPUnit2_MAIN_METHOD', 'Tester::main');

@require_once('PHPUnit2/TextUI/TestRunner.php');
@require_once('PHPUnit2/Framework/TestCase.php');

/**
 * functional test for Sabel Application
 *
 * @category   Test
 * @package    org.sabel.test
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Test_Functional extends PHPUnit2_Framework_TestCase
{
  protected $front     = null;
  protected $container = null;
  
  public function __construct()
  {
    $this->container = Container::create();
    $this->front = $this->container->load('sabel.controller.Front');
  }
  
  protected function request($uri)
  {
    return $this->front->ignition($uri);
  }
}

}