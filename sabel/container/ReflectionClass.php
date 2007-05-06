<?php

/**
 * Sabel Container
 *
 * @category   container
 * @package    org.sabel.core
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Container_ReflectionClass extends ReflectionClass
{
  private $implementClassName = "";
  private $reflectedClassName = "";
  
  public function __construct($name)
  {
    parent::__construct($name);
  }
  
  public function isImplementation()
  {
    return (!$this->isInterface() && !$this->isAbstract());
  }
}
