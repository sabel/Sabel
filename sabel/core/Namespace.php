<?php

class Sabel_Core_Namespace
{
  protected $name = '';
  protected $parent = null;
  protected $childs = array();
  
  protected static $namespaces = array();
  
  public function __construct($name = '', $parent = null)
  {
    $this->name = $name;
    if (!is_null($parent)) $parent->addNamespace($this);
  }
  
  public function setParent($parent)
  {
    $this->parent = $parent;
  }
  
  public function addNamespace($namespace)
  {
    if ($namespace instanceof Sabel_Core_Namespace) {
      $namespace->setParent($this);
      $this->childs[$namespace->getName()] = $namespace;
    }
  }
  
  public function getNamespace($name)
  {
    if (strpos($name, '.')) {
      $parts = explode('.', $name);
      $ns = $this;
      foreach ($parts as $part) {
        $ns = $ns->childs[$part];
      }
      return $ns;
    } else {
      return $this->childs[$name];
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
    self::$namespaces[join('.', $names)] = join('_', array_map('ucfirst', $names));
  }
  
  public function getName()
  {
    return $this->name;
  }
  
  public function getClassName($nsPath)
  {
    if (isset(self::$namespaces[$nsPath])) return self::$namespaces[$nsPath];
      
    $parts = explode('.', $nsPath);
    $names = array();
    $names[] = $this->name;
    $this->getParentName($names);
    $names = array_map('ucfirst', array_merge(array_reverse($names), $parts));
    
    return join('_', $names);
  }
  
  public function isRoot()
  {
    return ($this->name === '');
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
  
  public function link()
  {
    
  }
}