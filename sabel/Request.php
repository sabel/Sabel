<?php

/**
 * Sabel_Request
 *
 * @category   Request
 * @package    org.sabel.request
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Request extends Sabel_Object
{
  public static function create()
  {
    return Sabel::load("Sabel_Request_Web");
  }
  
  abstract public function getParameters();
  
  abstract public function getPostRequests();
    
  abstract public function hasParameter($name);
  
  abstract public function getParameter($name);
  
  abstract public function __toString();
}