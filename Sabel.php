<?php

define('SABEL', true);

class Sabel
{
  public static function initializeApplication()
  {
    if (ENVIRONMENT === 'development') {
      $c = Container::create();
      $dt = new DirectoryTraverser();
      $dt->visit(new ClassCombinator(SABEL_CLASSES, null, false));
      $dt->visit(new SabelClassRegister($c));
      $dt->traverse();
      unset($dt);
      $dt = new DirectoryTraverser(RUN_BASE);
      $dt->visit(new ClassCombinator(APP_CACHE, RUN_BASE, false, 'app'));
      $dt->visit(new ClassCombinator(LIB_CACHE, RUN_BASE, false, 'lib'));
      $dt->visit(new ClassCombinator(SCM_CACHE, RUN_BASE, false, 'schema'));
      $dt->visit(new ClassCombinator(INJ_CACHE, RUN_BASE, false, 'injections'));
      $dt->visit(new AppClassRegister($c));
      $dt->traverse();
      if (!defined('TEST_CASE')) require_once(SABEL_CLASSES);
      require_once(APP_CACHE);
      require_once(LIB_CACHE);
      require_once(SCM_CACHE);
      require_once(INJ_CACHE);
      
      $file = fopen(RUN_BASE . '/cache/container.cache', 'w');
      fputs($file, serialize($c->getClasses()));
      fclose($file);
    } else {
      $file = @fopen(RUN_BASE . '/cache/container.cache', 'r');
      if ($file) {
        $c = Container::create();
        $c->setClasses(unserialize(fgets($file)));
        require_once(SABEL_CLASSES);
        require_once(APP_CACHE);
        require_once(LIB_CACHE);
        require_once(SCM_CACHE);
        require_once(INJ_CACHE);
      } else {
        $file = fopen(RUN_BASE . '/cache/container.cache', 'w');
        $c = Container::create();
        $dt = new DirectoryTraverser();
        $dt->visit(new ClassCombinator(SABEL_CLASSES, null, false));
        $dt->visit(new SabelClassRegister($c));
        $dt->traverse();
        unset($dt);
        $dt = new DirectoryTraverser(RUN_BASE);
        $dt->visit(new ClassCombinator(APP_CACHE, RUN_BASE, false, 'app'));
        $dt->visit(new ClassCombinator(LIB_CACHE, RUN_BASE, false, 'lib'));
        $dt->visit(new ClassCombinator(SCM_CACHE, RUN_BASE, false, 'schema'));
        $dt->visit(new ClassCombinator(INJ_CACHE, RUN_BASE, false, 'injections'));
        $dt->visit(new AppClassRegister($c));
        $dt->traverse();
        require_once(SABEL_CLASSES);
        require_once(APP_CACHE);
        require_once(LIB_CACHE);
        require_once(SCM_CACHE);
        require_once(INJ_CACHE);
        
        fputs($file, serialize($c->getClasses()));
      }
    }
  }
}

/**
 * class Container has all of Sabel classes and Sabel Application.
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Container
{
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
      self::$classes[$key] = NameResolver::resolvDirectoryPathToClassName($path);
    }
    
    self::$classes[$key] = $name;
  }
  
  public function load($name, $mode = null)
  {
    static $instances;
    
    $className = $this->resolvShortClassName(self::$classes[$name]);
    
    $rc = new ReflectionClass($className);
    $di = Sabel_Container_DI::create();
    
    if ($rc->hasMethod('__construct')) {
      $ins = $di->load($className, '__construct');
      $instance = new Sabel_Injection_Injector($ins);
    } else {
      $instance = new Sabel_Injection_Injector(new $className());
    }
    
    $this->setter($instance);
    return $instance;
  }
  
  public function instanciate($name)
  {
    $className = $this->resolvShortClassName(self::$classes[$name]);
    return new $className();
  }
  
  protected function setter($injection)
  {
    $reflection = $injection->getReflection();
    $target     = $injection->getTarget();
    
    $annotations = array();
    foreach ($reflection->getProperties() as $property) {
      $annotations[] = Sabel_Annotation_Reader::getAnnotationsByProperty($property);
    }
    
    if (count($annotations) === 0) return;
    
    foreach ($annotations as $entries) {
      if (count($entries) === 0) continue;
      foreach ($entries as $annotation) {
        if (isset($annotation['implementation'])) {
          $className = $annotation['implementation']->getContents();
          $ins = new Sabel_Injection_Injector(new $className());
          $setter = 'set'. ucfirst($className);
          if (isset($annotation['setter'])) {
            $setter = $annotation['setter']->getContents();
            $target->$setter($ins);
          } else if ($reflection->hasMethod($setter)) {
            $target->$setter($ins);
          }
        }
      }
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
  
  protected function resolvShortClassName($className)
  {
    if (!class_exists($className)) {
      $classNameParts = explode('_', $className);
      for ($count = 0; $count < count($classNameParts); ++$count) {
        array_shift($classNameParts);
        $className = implode('_', $classNameParts);
        if (class_exists($className)) break;
      }
    }
    
    return $className;
  }
  
  public function getClasses()
  {
    return self::$classes;
  }
  
  public function setClasses($classes){
    self::$classes = $classes;
  }
}

class NameResolver
{
  
  public static function resolvClassNameToDirectoryPath($target)
  {
    $namespace = self::resolvClassNameToNameSpace($target);
    return str_replace('.', '/', $namespace) . '.php';
  }
  
  public static function resolvClassNameToNameSpace($target)
  {
    $parts = explode('_', $target);
    
    if (count($parts) === 1) return $target;
    
    $className = array_pop($parts);
    $parts = array_map('strtolower', $parts);
    array_push($parts, $className);
    return implode('.', $parts);
  }
  
  public static function resolvDirectoryPathToClassName($target)
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
  
  public static function resolvDirectoryPathToNameSpace($target)
  {
    $className = self::resolvDirectoryPathToClassName($target);
    return self::resolvClassNameToNameSpace($className);
  }
}

/**
 * Register application classes from application directories.
 *
 */
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

/**
 * Register sabel specific classes from sabel directories.
 *
 */
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
    $parts = explode('/', $value);
    
    if ($parts[0] === $this->strictDirectory) {
      $className = NameResolver::resolvDirectoryPathToClassName($value);
      $namespaceName = NameResolver::resolvDirectoryPathToNameSpace($value);
      $this->container->regist($namespaceName, $className);
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
    if (!$this->file) throw new Exception("{$path} can't open.");
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
      if (!$e->isDot() && !preg_match('/^\..*/', $e->getFileName()) && $e->isDir()) {
        foreach ($this->visitors as $visitor) $visitor->accept($entry, 'dir');
        $this->traverse(new DirectoryIterator($child));
      } else if (!$e->isDot() && !preg_match('%\/\..*%', $entry)) {
        if ($entry === 'sabel/db/schema/Common.php' || $entry === 'sabel/db/schema/General.php') break;
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

if (function_exists('singleton')) {
  function __singleton($classpath)
  {
    return Container::create()->load($classpath, 'singleton');
  }
} else {
  function singleton($classpath)
  {
    return Container::create()->load($classpath, 'singleton');
  }
}
