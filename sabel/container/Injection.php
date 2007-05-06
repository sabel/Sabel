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
abstract class Sabel_Container_Injection
{
  private $binds = array();
  private $constructes = array();
  
  abstract function configure();
  
  /**
   * bind interface to implementation
   *
   * @param string $interface name of interface
   * @return Sabel_Container_Bind
   */
  public function bind($interface)
  {
    $bind = new Sabel_Container_Bind($interface);
    $this->binds[$interface] = $bind;
    return $bind;
  }
  
  public function bindConstruct($className)
  {
    $construct = new Sabel_Container_Construct($className);
    $this->constructes[$className] = $construct;
    return $construct;
  }
  
  public function hasConstruct()
  {
    return (count($this->constructes) >= 1);
  }
  
  public function getConstruct($className)
  {
    return $this->constructes[$className];
  }
  
  public function getBinds()
  {
    return $this->binds;
  }
}
