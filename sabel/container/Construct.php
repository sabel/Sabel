<?php

/**
 * Sabel Container
 *
 * @category   Container
 * @package    org.sabel.container
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2002-2006 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Container_Construct
{
  private $constructs = array();
  private $source = "";
  
  public function __construct($className)
  {
    $this->source = $className;
  }
  
  public function with($className)
  {
    $this->constructs[] = $className;
    return $this;
  }
  
  public function hasConstruct()
  {
    return (count($this->constructs) >= 1);
  }
  
  public function getConstructs()
  {
    return $this->constructs;
  }
  
  public function isClass()
  {
    return (class_exists($this->construct));
  }
  
  public function isLiteral()
  {
    if (is_string($this->construct)) {
      return true;
    } elseif (is_numeric($this->construct)) {
      return true;
    } elseif (is_bool($this->construct)) {
      return false;
    }
  }
}
