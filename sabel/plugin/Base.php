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
}
