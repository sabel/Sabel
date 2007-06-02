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
abstract class Sabel_Plugin_Base
{
  const ON_CREATE_CONTROLLER = "onCreateController";
  const ON_BEFORE_ACTION     = "onBeforeAction";
  const ON_AFTER_ACTION      = "onAfterAction";
  const ON_REDIRECT          = "onRedirect";
  const ON_EXCEPTION         = "onException";
  const ON_EXECUTE_ACTION    = "onExecuteAction";
  
  protected $controller  = null;
  protected $destination = null;
  
  public function setController($controller)
  {
    $this->controller = $controller;
  }
  
  public function setDestination($destination)
  {
    $this->destination = $destination;
  }
  
  public function onBeforeAction(){}
  public function onAfterAction(){}
  public function onRedirect($to){}
  public function onException($exception){}
  public function onExecuteAction($method){}
  public function onCreateController($destination){}
}
