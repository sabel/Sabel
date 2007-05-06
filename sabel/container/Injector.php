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
class Sabel_Container_Injector
{
  private $injection = null;
  
  public function __construct(Sabel_Container_Injection $injection)
  {
    $this->injection = $injection;
    $this->injection->configure();
  }
  
  public function getInstance($className)
  {
    $c = new Sabel_Container_DI();
    
    if ($this->injection->hasConstruct()) {
      $construct = $this->injection->getConstruct($className);
      if ($construct->isClass()) {
        $dependClassName = $construct->getConstruct();
        $instance = new $className(new $dependClassName);
      } else {
        $literal = $construct->getConstruct();
        $instance = new $className($literal);
      }
    } else {
      $instance = $c->load($className);
    }
    
    foreach ($this->injection->getBinds() as $name => $bind) {
      $defaultInjectionMethod = "set" . ucfirst($name);
      $implClassName = $bind->getImplementation();
      $instance->$defaultInjectionMethod($c->load($implClassName));
    }
    
    return $instance;
  }
}
