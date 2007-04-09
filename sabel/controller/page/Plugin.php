<?php

/**
 * Sabel Controller Page Plugin
 *
 * @abstract
 * @category   core
 * @package    org.sabel.object
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Controller_Page_Plugin
{
  public function onBeforeAction($controller){}
  public function onAfterAction($controller){}
  public function onRedirect($controller){}
  public function onException($controller, $exception){}
  public function onCreateController($controller, $candidate){}
}
