<?php

define('SABEL', true);

class Sabel
{
  public static function initializeApplication()
  {
    $conbinators = array(new ClassCombinator(APP_CACHE, RUN_BASE, false, 'app'),
                         new ClassCombinator(LIB_CACHE, RUN_BASE, false, 'lib'),
                         new ClassCombinator(INJ_CACHE, RUN_BASE, false, 'aspects'),
                         new ClassCombinator(SCM_CACHE, RUN_BASE, false, 'schema'));
                         
    if (ENVIRONMENT === 'development') {
      $c = Container::create();
      
      $dt = new DirectoryTraverser();
      $cc = new ClassCombinator(SABEL_CLASSES, null, false);
      $dt->visit($cc);
      $dt->visit(new SabelClassRegister($c));
      $dt->traverse();
      $cc->write();
      unset($dt);
      
      $dt = new DirectoryTraverser(RUN_BASE);
      foreach ($conbinators as $conbinator) $dt->visit($conbinator);
      $dt->visit(new AppClassRegister($c));
      $dt->traverse();
      foreach ($conbinators as $conbinator) $conbinator->write();
      
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
        $cc = new ClassCombinator(SABEL_CLASSES, null, false);
        $dt->visit($cc);
        $dt->visit(new SabelClassRegister($c));
        $dt->traverse();
        $cc->write();
        unset($dt);
        
        $dt = new DirectoryTraverser(RUN_BASE);
        foreach ($conbinators as $conbinator) $dt->visit($conbinator);
        $dt->visit(new AppClassRegister($c));
        $dt->traverse();
        foreach ($conbinators as $conbinator) $conbinator->write();
        
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
      $instance = new Sabel_Aspect_DynamicProxy($ins);
    } else {
      $instance = new Sabel_Aspect_DynamicProxy(new $className());
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
          $ins = new Sabel_Aspect_DynamicProxy(new $className());
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
    $dir = str_replace('.', '/', $namespace);
    $parts = explode('/', $dir);
    return join('/', $parts).'.php';
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
  protected $path = '';
  protected $lineTrim   = null;
  protected $base = '';
  protected $strict = '';
  
  protected $files = array();
  protected $filesOfHasParent = array();
  
  public function __construct($path, $base = null, $lineTrim = true, $strict = 'sabel')
  {
    $this->path = $path;
    $this->strict = $strict;
    $this->lineTrim = $lineTrim;
    $this->files = new ClassFiles();
    
    if (is_null($base)) {
      $this->base = dirname(__FILE__) . '/';
    } else {
      $this->base = $base . '/';
    }
  }
  
  public function accept($value, $type, $child = null)
  {
    $parts = explode('/', $value);
    $value = $this->base . $value;
    
    if ($type === 'file' && preg_match('%.*\.php%', $value)) {
      if ($parts[0] === $this->strict) {
        if (!$fp = fopen($value, 'r')) throw new Exception("{$value} can't open.");
        $classFile = new ClassFile($value);
        while (!feof($fp)) $classFile->addLine(trim(fgets($fp)));
        fclose($fp);
        $this->files->add($classFile);
      }
    }
  }
  
  public function write()
  {
    $buf = array();
    $buf[] = "<?php \n";
    
    $this->files->findChilds();
    $files = $this->files->gets();
    
    $conflicts = array();
    
    foreach ($files as $file) {
      if ($file->hasChild()) {
        $conflicts[] = $file->getSelf();
        foreach ($file->getLines() as $line) {
          $buf[] = $line;
        }
      }
    }
    
    foreach ($files as $file) {
      if ($file->hasBoth() && !in_array($file->getSelf(), $conflicts)) {
        $conflicts[] = $file->getSelf();
        foreach ($file->getLines() as $line) {
          $buf[] = $line;
        }
      }
    }
    
    foreach ($files as $file) {
      if ($file->hasParent() && !in_array($file->getSelf(), $conflicts)) {
        $conflicts[] = $file->getSelf();
        foreach ($file->getLines() as $line) {
          $buf[]= $line;
        }
      }
    }
    
    foreach ($files as $file) {
      if ($file->hasnt() && !in_array($file->getSelf(), $conflicts)) {
        foreach ($file->getLines() as $line) {
          $buf[]= $line;
        }
      }
    }
    
    $fp = fopen($this->path, 'w');
    foreach ($buf as $b) {
      fputs($fp, $b);
    }
    fclose($fp);
  }
  
  protected function isExtends($line)
  {
    return preg_match('%^(abstract class|class).*(extends|implements).*%', $line);
  }
  
  protected function isLineValid($line)
  {
    return ($line != '<?php' && $line != '?>' && !preg_match('%\/\/ %', $line));
  }
}

class ClassFiles
{
  protected $classFiles = array();
  
  public function add($classFile)
  {
    if (!$classFile instanceof ClassFile) throw new Exception("it's not ClassFile");
    $this->classFiles[] = $classFile;
  }
  
  public function gets()
  {
    return $this->classFiles;
  }
  
  public function findChilds()
  {
    $files = $this->classFiles;
    foreach ($files as $parent) {
      foreach ($files as $child) {
        if ($child->hasParent() && $child->getParent() === $parent->getSelf()) {
          $parent->addChild($child->getSelf());
        }
      }
    }
  }
}

class ClassFile
{
  protected $self = '';
  protected $parent = '';
  protected $childs  = array();
  
  protected $hasParent = false;
  protected $hasChild  = false;
  
  protected $lineOfClassDefine = '';
  protected $lines = array();
  
  protected $filePath = '';
  
  /**
   *  0 no state
   *  5 single quote start
   * 10 double quote start
   * 15 may be start line comment
   * 20 start line comment
   * 25 start here doc
   */
  protected $state = 0;
  protected $here  = null;
  
  public function __construct($filepath)
  {
    $this->setFilePath($filepath);
  }
  
  public function getSelf()
  {
    return $this->self;
  }
  
  public function setParent($className)
  {
    $this->parent = $className;
    $this->hasParent = true;
  }
  
  public function getParent()
  {
    return $this->parent;
  }
  
  public function addChild($className)
  {
    $this->childs[] = $className;
    $this->hasChild = true;
  }
  
  public function getChilds()
  {
    return $this->childs;
  }
  
  public function hasParent()
  {
    return $this->hasParent;
  }
  
  public function hasChild()
  {
    return $this->hasChild;
  }
  
  public function hasBoth()
  {
    return ($this->hasParent() && $this->hasChild());
  }
  
  public function hasnt()
  {
    return (!$this->hasParent() && !$this->hasChild());
  }
  
  public function setFilePath($filepath)
  {
    $this->filePath = $filepath;
  }
  
  public function getFilePath()
  {
    return $this->filePath;
  }
  
  public function addLine($line)
  {
    if (!$this->isLineValid($line)) return false;
    
    $this->removeLineComment($line);
    
    $result = $this->isExtends($line);
    if ($result[0]) {
      $this->hasParent = true;
      $this->lineOfClassDefine = $line;
      
      $this->self   = trim($result[1][2]);
      $this->parent = trim($result[1][4]);
    } else {
      $result = $this->isClassDefine($line);
      if ($result[0]) {
        $this->self = trim($result[1][2]);
        $this->lineOfClassDefine = $line;
      }
    }
    
    $line = $this->removeLineComment($line);
    
    $this->lines[] = $line . "\n";
  }
  
  public function getLineOfClassDefine()
  {
    return $this->lineOfClassDefine;
  }
  
  public function getLine($index)
  {
    return $this->lines[$index];
  }
  
  public function setLines($lines)
  {
    $this->lines = $lines;
  }
  
  public function getLines()
  {
    return $this->lines;
  }
  
  protected function isExtends($line)
  {
    $matches = array();
    $pat = '%^(abstract class|interface|class)(.*)(extends|implements)(.*)%';
    $result = preg_match($pat, $line, $matches);
    return array($result, $matches);
  }
  
  protected function isClassDefine($line)
  {
    $matches = array();
    $pat = '%^(abstract class|interface|class)(.*)%';
    $result = preg_match($pat, $line, $matches);
    return array($result, $matches);
  }
  
  protected function isLineValid($line)
  {
    if ($line === '') return false;
    if ($line === '<?php') return false;
    if ($line === '?>') return false;
    
    return true;
  }
  
  protected function removeLineComment($line)
  {
    if ($this->state !== 5 && $this->state !== 10 && $this->state !== 25) $this->state = 0;
    
    if (!is_null($this->here) && $line === $this->here.';') {
      $this->here  = null;
      $this->state = 0;
    }
    
    if ($this->state === 25) {
      return $line;
    }
    
    for ($i = 0; $i < strlen($line); $i++) {
      $c = $line[$i];
    
      if ($this->state === 0) {
        switch ($c) {
          case "'":
            $this->state = 5;
            break;
          case '"':
            $this->state = 10;
            break;
          case '/':
            $this->state = 15;
            break;
        }
        
        if ($c === '<' && $line[$i+1] === '<' && $line[$i+2] === '<') {
          $this->state = 25;
          $this->here = substr($line, $i+3);
        } 
        continue;
      }
      
      
      if ($this->state === 5  && $c === "'") {
        $this->state = 0;
        continue;
      }
      
      if ($this->state === 10 && $c === '"') {
        $this->state = 0;
        continue;
      }
      
      if ($this->state === 15 && $c === '/') {
        $line = substr($line, 0, $i-1);
        break;
      }
    }
    
    return $line;
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