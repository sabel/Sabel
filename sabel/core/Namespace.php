<?php

/**
 * Sabel_Core_Namespace
 *
 * @category   Namespace
 * @package    org.sabel.Namespace
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Core_Namespace
{
  protected $name = '';
  
  /**
   * pointer to parent object
   * 
   * @var object $parent instance of Sabel_Core_Namespace
   */
  protected $parent = null;
  protected $childs = array();
  protected $classes = array();
  
  protected static $namespaces = array();
  
  public function __construct($name = '', $parent = null)
  {
    $this->name = $name;
    if (!is_null($parent)) $parent->addNamespace($this);
  }
  
  public function getName()
  {
    return $this->name;
  }
  
  public function isRoot()
  {
    return ($this->name === '');
  }
  
  /**
   * set parent namespace
   *
   * @param object $ns instance of Sabel_Core_Namespace
   * @return void
   * @throws Sabel_Exception_Runtime
   */
  public function setParent($parent)
  {
    if (!$parent instanceof Sabel_Core_Namespace)
      throw new Sabel_Exception_Runtime("$ns isn't Namespace");
      
    $this->parent = $parent;
  }
  
  /**
   * add child namespace
   *
   * @param object $ns instance of Sabel_Core_Namespace
   * @return void
   * @throws Sabel_Exception_Runtime
   */
  public function addNamespace($ns)
  {
    if (!$ns instanceof Sabel_Core_Namespace)
      throw new Sabel_Exception_Runtime("$ns isn't Namespace");
    
    $ns->setParent($this);
    $this->childs[$ns->getName()] = $ns;
  }
  
  /**
   * get namespace
   *
   * @param string $entry if entry include .(dot) entry to be entries
   * @return mixied Sabel_Core_Namespace or null.
   */
  public function getNamespace($entry)
  {
    if (strpos($entry, '.')) {
      $entries = $entry;
      // absolute path e.g. sabel.core.Foo
      $nsEntries = explode('.', $entries);
      $temporaryNS = $this;
      foreach ($nsEntries as $entry) {
        if (isset($temporaryNS->childs[$entry])) {
          $temporaryNS = $temporaryNS->childs[$entry];
        }
      }
      return $temporaryNS;
    } else {
      // find from child
      if (isset($this->childs[$entry])) {
        return $this->childs[$entry];
      } else {
        return false;
      }
    }
  }
  
  public function addClass($className)
  {
    if (is_null($this->parent)) throw new Exception("parent can't be null");
    
    $names = array();
    $names[] = $this->name;
    $this->getParentName($names);
    $names = array_reverse($names);
    $names[] = $className;
    $this->classes[] = $className;
    self::$namespaces[join('.', $names)] = join('_', array_map('ucfirst', $names));
  }
  
  public function getClasses()
  {
    return $this->classes;
  }
  
  public function getClassName($entry)
  {
    if (isset(self::$namespaces[$entry])) return self::$namespaces[$entry];
      
    $entries = explode('.', $entry);
    $names = array();
    $names[] = $this->name;
    $this->getParentName($names);
    $names = array_map('ucfirst', array_merge(array_reverse($names), $entries));
    
    return join('_', $names);
  }
  
  public function getParentName(&$stack)
  {
    if ($this->parent !== null) {
      if (!$this->parent->isRoot()) {
        $stack[] = $this->parent->getName();
      }
      $this->parent->getParentName($stack);
    }
  }
  
  public function getChilds()
  {
    return $this->childs;
  }
}