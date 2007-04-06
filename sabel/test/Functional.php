<?php

if (!defined('PHPUnit_MAIN_METHOD'))
  define('PHPUnit_MAIN_METHOD', 'Tester::main');

require_once ('PHPUnit/TextUI/TestRunner.php');
require_once ('PHPUnit/Framework/TestCase.php');

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

class Sabel_Controller_Plugin_TestRedirecter extends Sabel_Controller_Page_Plugin
{    
  public function onRedirect($controller, $to = null)
  {
    $host = Sabel_Environment::get("http_host");
    
    $absolute = 'http://' . $host;
    $redirect = 'Location: ' . $absolute . '/' . $to;
  }
}