<?php

/**
 * class Container has all of Sabel classes and Sabel Application.
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Container
{
  const SINGLETON = 'singleton';
  const NORMAL    = 'normal';
  
  protected static $classes   = array();
  
  public function regist($name, $path)
  {
    $d2c = new DirectoryPathToClassNameResolver();
    self::$classes[$name] = $d2c->resolv($path);
  }
  
  public function load($name, $mode = self::NORMAL)
  {
    static $instances;
    
    if ($mode === self::SINGLETON) {
      if (isset($instances[$name])) {
        return new Sabel_Injection_Injector($instances[$name]);
      } else {
        $className = self::$classes[$name];
        $instances[$name] = $instance = new $className();
        return new Sabel_Injection_Injector($instance);
      }
    } else {
      $class = self::$classes[$name];
      if (!$class) throw new Exception('');
      $ins = new $class();
      return new Sabel_Injection_Injector($ins);
    }
  }
}

interface ClassNameMappingResolver
{
  public function resolv($target);
}

class ClassNameToNameSpaceResolver implements ClassNameMappingResolver
{
  public function resolv($target)
  {
    $parts = explode('_', $target);
    
    if (count($parts) === 1) return $target;
    
    $className = array_pop($parts);
    $parts = array_map('strtolower', $parts);
    array_push($parts, $className);
    return implode('.', $parts);
  }
}

class DirectoryPathToNameSpaceResolver implements ClassNameMappingResolver
{
  public function resolv($target)
  {
    $d2c = new DirectoryPathToClassNameResolver();
    $c2n = new ClassNameToNameSpaceResolver();
    return $c2n->resolv($d2c->resolv($target));
  }
}

class DirectoryPathToClassNameResolver implements ClassNameMappingResolver
{
  public function resolv($target)
  {
    $parts = explode('/', $target);
    $buf = '';
    $pos = 0;
    
    foreach ($parts as $part) {
      if ((count($parts) - 1) === $pos) {
        $last = explode('.', $parts[count($parts)-1]);
        $buf .= $last[0];
      } else {
        $buf .= ucfirst($part) . '_';
      }
      ++$pos;
    }
    
    return $buf;
  }
}

class ClassRegister
{
  protected $container;
  protected $strictDirectory;
  
  public function __construct($container, $strictDirectory = 'sabel')
  {
    $this->container = $container;
    $this->strictDirectory = $strictDirectory;
  }
  
  public function accept($value, $type)
  {
    $d2c = new DirectoryPathToClassNameResolver();
    $resolver = new DirectoryPathToNameSpaceResolver();
    $parts = explode('/', $value);
    
    if ($parts[0] === $this->strictDirectory) {
      $this->container->regist($resolver->resolv($value), $d2c->resolv($value));
    }
  }
}

class ClassCombinator
{
  protected $allclasses = null;
  protected $lineTrim   = null;
  
  public function __construct($lineTrim = true)
  {
    $this->allclasses = fopen('allclasses.php', 'w');
    fwrite($this->allclasses, '<?php ');
    $this->lineTrim = $lineTrim;
  }
  
  public function accept($value, $type)
  {
    $parts = explode('/', $value);
    
    if ($type === 'file') {
      if ($parts[0] === 'sabel') {
        if (!$fp = fopen($value, 'r')) throw new Exception("{$value} can't open.");
        while (!feof($fp)) {
          $line = trim(fgets($fp));
          if ($this->lineTrim) {
            if ($this->isLineValid($line)) fputs($this->allclasses, $line);
          } else {
            if ($this->isLineValid($line)) fputs($this->allclasses, $line . "\n");
          }
        }
      }
    }
  }
  
  protected function isLineValid($line)
  {
    return ($line != '<?php' && $line != '?>' && !preg_match('%\/\/ %', $line));
  }
}

class DirectoryTraverser
{
  protected $directories = null;
  protected $dir = '';
  protected $visitors = array();
  
  public function __construct($dir = null)
  {
    if ($dir) {
      $this->dir = dirname(realpath(__FILE__)) . '/' .$dir;
    } else {
      $this->dir = dirname(realpath(__FILE__));
    }
    
    $this->directories = new DirectoryIterator($this->dir);
  }
  
  public function visit($visitor)
  {
    $this->visitors[] = $visitor;
  }
  
  public function traverse($fromElement = null)
  {
    $element = (is_null($fromElement)) ? $this->directories : $fromElement;
    foreach ($element as $e) {
      $child = $e->getPath() .'/'. $e->getFileName();
      $entry = ltrim(str_replace($this->dir, '', $child), '/');
      
      if (!$e->isDot() && $e->isDir()) {
        foreach ($this->visitors as $visitor) $visitor->accept($entry, 'dir');
        $this->traverse(new DirectoryIterator($child));
      } else if (!$e->isDot() && ! preg_match('%\/\..*%', $entry)) {
        foreach ($this->visitors as $visitor) $visitor->accept($entry, 'file',  $child);
      }
    }
  }
}