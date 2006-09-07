<?php

define('SABEL', true);

/**
 * class Container has all of Sabel classes and Sabel Application.
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Container
{
  const NORMAL    = 'normal';
  const SINGLETON = 'singleton';
  
  protected static $instance = null;
  protected static $classes  = array();
  
  public static function create()
  {
    if (is_null(self::$instance)) {
      self::$instance = new self();
    }
    return self::$instance;
  }
  
  public function regist($key, $name, $path = null)
  {
    if (!is_null($path)) {
      $d2c = new DirectoryPathToClassNameResolver();
      self::$classes[$key] = $d2c->resolv($path);
    }
    
    self::$classes[$key] = $name;
  }
  
  public function load($name, $mode = null)
  {
    static $instances;
    $className = self::$classes[$name];
    
    if (!class_exists($className)) {
      $classNameParts = explode('_', $className);
      while(true) {
        array_shift($classNameParts);
        $className = implode('_', $classNameParts);
        if (class_exists($className)) break;
      }
    }
    
    $rc = new ReflectionClass($className);
    $di = Sabel_Container_DI::create();
    
    if ($rc->hasMethod('__construct')) {
      $ins = $di->load($className, '__construct');
      $injection = new Sabel_Injection_Injector($this, $ins);
      $injection->observe(new Sabel_Injection_Setter());
      return $injection;
    } else {
      $injection = new Sabel_Injection_Injector($this, new $className());
      $injection->observe(new Sabel_Injection_Setter());
      return $injection;
    }
  }
  
  public function isRegistered($classpath)
  {
    return (isset(self::$classes[$classpath]));
  }
  
  public function getRegisteredClasses()
  {
    return array_keys(self::$classes);
  }
  
  public function oad($name, $mode = self::NORMAL)
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


class AppClassRegister
{
  protected $container;
  
  public function __construct($container)
  {
    $this->container = $container;
  }
  public function accept($value, $type, $child = null)
  {
    if ($type === 'dir') return;
    
    $classpath = $this->makeClassPath($value);
    $className = $this->makeClassName($classpath);
    
    if (!is_null($classpath))
      $this->container->regist($classpath, $className);
  }
  
  protected function makeClassPath($value)
  {
    $parts = explode('/', $value);
    $fileName = $parts[count($parts)-1];
    list($file, $extention) = explode('.', $fileName);
    
    if ($extention == 'php') {
      if ($parts[0] === 'app' || $parts[0] === 'lib') {
        array_shift($parts);
        $parts[count($parts) - 1] = ucfirst($file);
        return implode('.', $parts);
      }
    }
  }
  
  protected function makeClassName($classPath)
  {
    $parts = explode('.', $classPath);
    $parts = array_map('ucfirst', $parts);
    return implode('_', $parts);
  }
}

class SabelClassRegister
{
  protected $container;
  protected $strictDirectory;
  
  public function __construct($container, $strictDirectory = 'sabel')
  {
    $this->container = $container;
    $this->strictDirectory = $strictDirectory;
  }
  
  public function accept($value, $type, $child = null)
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
  protected $file = null;
  protected $lineTrim   = null;
  protected $base = '';
  protected $strict = '';
  
  public function __construct($path, $base = null, $lineTrim = true, $strict = 'sabel')
  {
    $this->strict = $strict;
    
    if (is_null($base)) {
      $this->base = dirname(__FILE__) . '/';
    } else {
      $this->base = $base . '/';
    }
    
    $this->file = fopen($path, 'w');
    fwrite($this->file, '<?php ');
    $this->lineTrim = $lineTrim;
  }
  
  public function accept($value, $type, $child = null)
  {
    $parts = explode('/', $value);
    $value = $this->base . $value;
    
    if ($type === 'file' && preg_match('%.*\.php%', $value)) {
      if ($parts[0] === $this->strict) {
        if (!$fp = fopen($value, 'r')) throw new Exception("{$value} can't open.");
        while (!feof($fp)) {
          $line = trim(fgets($fp));
          if ($this->lineTrim) {
            if ($this->isLineValid($line)) fputs($this->file, $line);
          } else {
            if ($this->isLineValid($line)) fputs($this->file, $line . "\n");
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
      $this->dir = $dir;
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
      } else if (!$e->isDot() && !preg_match('%\/\..*%', $entry)) {
        foreach ($this->visitors as $visitor) $visitor->accept($entry, 'file',  $child);
      }
    }
  }
}

if (function_exists('create')) {
  function __create($classpath)
  {
    return Container::create()->load($classpath);
  }
} else {
  function create($classpath)
  {
    return Container::create()->load($classpath);
  }
}