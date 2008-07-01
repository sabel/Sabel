<?php

/**
 * Sabel Container
 *
 * @abstract
 * @category   Container
 * @package    org.sabel.container
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Container_Injection implements Sabel_Config
{
  private $weaverClass = "Sabel_Aspect_DynamicWeaver";
  
  private
    $binds,
    $aspects,
    $lifecycle,
    $constructs = array();
  
  /**
   * bind interface to implementation
   *
   * @param string $interface name of interface
   * @return Sabel_Container_Bind
   */
  public function bind($interface)
  {
    $bind = new Sabel_Container_Bind($interface);
    $this->binds[$interface][] = $bind;
    
    return $bind;
  }
  
  /**
   * bind constructer with object or value
   *
   * @param string $interface name of interface
   * @return Sabel_Container_Bind
   */
  public function construct($className)
  {
    $construct = new Sabel_Container_Construct($className);
    $this->constructs[$className] = $construct;
    
    return $construct;
  }
  
  /**
   * bind aspect
   *
   * @param string $interface name of interface
   * @return Sabel_Container_Bind
   */
  public function aspect($className)
  {
    if (!interface_exists("Sabel_Aspect_Advice")) {
      $SABEL_ASPECT = "sabel" . DIRECTORY_SEPARATOR . "aspect" . DIRECTORY_SEPARATOR;
      
      require ($SABEL_ASPECT . "Interfaces.php");
      require ($SABEL_ASPECT . "Matchers.php");
      require ($SABEL_ASPECT . "Pointcuts.php");
      require ($SABEL_ASPECT . "Advisors.php");
      require ($SABEL_ASPECT . "Introduction.php");
      require ($SABEL_ASPECT . "Interceptors.php");
    }
    
    $aspect = new Sabel_Container_Aspect($className);
    $this->aspects[$className] = $aspect;
    
    return $aspect;
  }
  
  public function weaver($weaverClass)
  {
    $this->weaverClass = $weaverClass;
    return $this;
  }
  
  public function getWeaver()
  {
    return $this->weaverClass;
  }
  
  public function getAspect($className)
  {
    if ($this->hasAspect($className)) {
      return $this->aspects[$className];
    } else {
      return false;
    }
  }
  
  public function getAspects()
  {
    return array_values($this->aspects);
  }
  
  public function hasAspect($className)
  {
    return isset($this->aspects[$className]);
  }
  
  public function hasConstruct($className)
  {
    return array_key_exists($className, $this->constructs);
  }
  
  public function getConstruct($className)
  {
    if (isset($this->constructs[$className])) {
      return $this->constructs[$className];
    } else {
      return false;
    }
  }
  
  public function getBinds()
  {
    return $this->binds;
  }
  
  public function hasBinds()
  {
    return (count($this->binds) >= 1);
  }
  
  public function hasBind($className)
  {
    return isset($this->binds[$className]);
  }
  
  public function getBind($className)
  {
    if ($this->hasBind($className)) {
      return $this->binds[$className];
    } else {
      return false;
    }
  }
  
  public function lifecycle($className)
  {
    $lifecycle = new Sabel_Container_LifeCycle();
    $this->lifecycle[$className] = $lifecycle;
    
    return $lifecycle;
  }
  
  public function getLifecycle($className)
  {
    return $this->lifecycle[$className];
  }
  
  public function hasLifeCycle($className)
  {
    return isset($this->lifecycle[$className]);
  }
}

class Sabel_Container_LifeCycle
{
  private $lifecycle = "";
  private $backend   = "";
  
  public function in($lifecycle)
  {
    // @todo check everything. is_string, supported type
    $this->lifecycle = $lifecycle;
    return $this;
  }
  
  public function backend($name)
  {
    $this->backend = $name;
    return $this;
  }
  
  public function isApplication()
  {
    return ($this->lifecycle === "Application");
  }
  
  public function getBackend($type)
  {
    $className = $this->backend;
    return new $className($type);
  }
}
