<?php

if (!defined('PHPUnit_MAIN_METHOD'))
  define('PHPUnit_MAIN_METHOD', 'Tester::main');

require_once('PHPUnit/TextUI/TestRunner.php');
require_once('PHPUnit/Framework/TestCase.php');

//Sabel::using("Sabel_Request");

/**
 * functional test for Sabel Application
 *
 * @category   Test
 * @package    org.sabel.test
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Test_Functional extends PHPUnit_Framework_TestCase
{
  protected function request($uri, $storage = null)
  {
    $aFrontController = new Sabel_Controller_Front();
    
    $aFrontController->plugin
                     ->add(new Sabel_Controller_Plugin_Volatile())
                     ->add(new Sabel_Controller_Plugin_Filter())
                     ->add(new Sabel_Controller_Plugin_View())
                     ->add(new Sabel_Controller_Plugin_ExceptionHandler())
                     ->add(new Sabel_Controller_Plugin_TestRedirecter());
                     
    return $aFrontController->ignition($uri, $storage);
  }
  
  protected function assertHtmlElementEquals($expect, $id, $html)
  {
    $doc = new DomDocument();
    @$doc->loadHTML($html);
    $element = $doc->getElementById($id);
    
    $this->assertEquals($expect, $element->nodeValue);
  }
}

//Sabel::using("Sabel_Controller_Page_Plugin");
class Sabel_Controller_Plugin_TestRedirecter implements Sabel_Controller_Page_Plugin
{  
  public function onException($controller, $exception) {}
  public function onBeforeAction($controller) {}
  public function onAfterAction($controller) {}
  
  public function onRedirect($controller, $to = null)
  {
    $host = (isset($_SERVER["HTTP_HOST"])) ? $_SERVER["HTTP_HOST"] : "localhost";
    
    $absolute = 'http://' . $host;
    $redirect = 'Location: ' . $absolute . '/' . $to;
  }
}