<?php

define('SABEL', true);

class Sabel
{
  public static function initializeApplication()
  {
    $cacheFilepath = RUN_BASE . '/cache/container.cache';
    $roots = array();
    $roots[] = array(APP_CACHE, '/app/');
    $roots[] = array(LIB_CACHE, '/lib/');
    $roots[] = array(INJ_CACHE, '/aspects/');
    $roots[] = array(SCM_CACHE, '/schema/');
                         
    $c = Container::create();
    
    if (ENVIRONMENT !== 'development' && is_readable($cacheFilepath)) {
      $file = fopen($cacheFilepath, 'r');
      $c->setClasses(unserialize(fgets($file)));
      require_once(SABEL_CLASSES);
    } else {
      ClassFileStructureReader::create('sabel/')
        ->read()
        ->write(SABEL_CLASSES);
        
      if (!defined('TEST_CASE')) require_once(SABEL_CLASSES);
      
      foreach ($roots as $root) {
        ClassFileStructureReader::create($root[1], RUN_BASE)
          ->read()
          ->write($root[0]);
      }
      
      $file = fopen(RUN_BASE . '/cache/container.cache', 'w');
      fputs($file, serialize($c->getClasses()));
      fclose($file);
    }
    
    require_once(APP_CACHE);
    require_once(LIB_CACHE);
    require_once(SCM_CACHE);
    require_once(INJ_CACHE);
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
      self::$classes[$key] = NameResolver::resolvDirectoryPathToClassName($path);
    }
    
    self::$classes[$key] = $name;
  }
  
  public function load($name, $mode = null)
  {
    static $instances;
    
    //$className = $this->resolvShortClassName(self::$classes[$name]);
    $name = NameResolver::resolvNameSpaceToClassName($name);
    $className = $this->resolvShortClassName($name);
    
    $rc = new ReflectionClass($className);
    $di = Sabel_Container_DI::create();
    
    if ($rc->hasMethod('__construct')) {
      $ins = $di->load($className, '__construct');
      $instance = $this->proxynize($ins);
    } else {
      $instance = $this->proxynize(new $className());
    }
    
    $this->setter($instance);
    return $instance;
  }
  
  protected function setter($proxy)
  {
    $reflection = $proxy->getReflection();
    $target     = $proxy->getTargetClass();
    
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
          $ins = $this->proxynize(new $className());
          $setter = 'set'. ucfirst($className);
          if (isset($annotation['setter'])) {
            $setter = $annotation['setter']->getContents();
            $target->$setter($ins);
          } elseif ($reflection->hasMethod($setter)) {
            $target->$setter($ins);
          }
        }
      }
    }
  }
  
  protected function proxynize($class)
  {
    return new Sabel_Aspect_Proxy($class);
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
        if (class_exists($className, false)) break;
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
  public static function resolvNameSpaceToClassName($target)
  {
    $parts = array_map('ucfirst', explode('.', $target));
    return join('_', $parts);
  }
  
  public static function resolvClassNameToDirectoryPath($target, $toLower = true)
  {
    $namespace = self::resolvClassNameToNameSpace($target, $toLower);
    return str_replace('.', '/', $namespace) . '.php';
  }
  
  public static function resolvClassNameToNameSpace($target, $toLower = true)
  {
    $parts = explode('_', $target);
    
    if (count($parts) === 1) return $target;
    
    $className = array_pop($parts);
    if ($toLower) $parts = array_map('strtolower', $parts);
    $parts[] = $className;
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
  protected $self   = '';
  protected $parent = '';
  protected $childs = array();
  
  protected $hasParent = false;
  protected $hasChild  = false;
  
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
  
  public function addLines($lines)
  {
    foreach ($lines as $line) $this->addLine(rtrim($line));
  }
  
  public function addLine($line)
  {
    if (!$this->isLineValid($line)) return false;
    
    $result = $this->isExtends($line);
    if (isset($result)) {
      $this->hasParent = true;
      if (empty($this->self) || $this->self != trim($result[4])) {
        $this->self   = trim($result[2]);
        $this->parent = trim($result[4]);
      }
    } else {
      $result = $this->isClassDefine($line);
      if (isset($result)) {
        $this->self = trim($result[2]);
      }
    }
    
    $line = $this->removeLineComment($line);
    
    $this->lines[] = $line . "\n";
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
    $pat = '%^(abstract class|interface|class)(.*)(extends|implements)(.*)%';
    return (preg_match($pat, $line, $matches)) ? $matches : null;
  }
  
  protected function isClassDefine($line)
  {
    $pat = '%^(abstract class|interface|class) (.*)%';
    return (preg_match($pat, $line, $matches)) ? $matches : null;
  }
  
  protected function isLineValid($line)
  {
    if ($line === '') return false;
    if ($line === '<?') return false;
    if ($line === '<?php') return false;
    if ($line === '?>') return false;
    
    return true;
  }
  
  protected function removeLineComment($line)
  {
    $this->state = 0;
    for ($i = 0; $i < strlen($line); $i++) {
      $c = $line[$i];
      switch ($this->state) {
        case 0: // no state
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
            case '#':
              $this->state = 30;
              break;
            case '<':
              if ($line[$i+1] === '<' && $line[$i+2] === '<') {
                $this->here  = substr($line, $i+3);
                $this->state = 25;
              }
              break;
          }
          break;
        case 5: // single quote
          if ($c === "'"  && $line[$i-1] !== '\\') $this->state = 0;
          break;
        case 10: // double quote
          if ($c === '"' && $line[$i-1] !== '\\') $this->state = 0;
          break;
        case 15: // may be comment
          switch ($c) {
            case '*':
              $this->state = 20;
              break;
            case '/':
              $this->state = 30;
              break;
          }
          break;
        case 20: // multi line comment
          if ($c === '/' && $line[$i-1] === '*') $this->state = 0;
          break;
        case 25: // hereDoc
          if ($line === $this->here . ';') $this->state = 0;
          break;
      }
      if ($this->state === 30) {
        $this->state = 0;
        $line = substr($line, 0, $i-1);
      }
    }
    return $line;
  }
}

class ClassFileStructureReader
{
  protected $base = '';
  protected $dir = '';
  protected $directories = null;
  
  protected $files = null;
  protected $filesOfHasParent = array();
  
  private function __construct($dir, $base, $classFiles)
  {
    $this->base = (is_null($base)) ? dirname(realpath(__FILE__)).'/' : $base;
    $this->dir = $this->base . $dir;
    $this->directories = new DirectoryIterator($this->dir);
    $this->files = (is_null($classFiles)) ? new ClassFiles() : $classFiles;
  }
  
  public static function create($dir = null, $base = null, $classFiles = null)
  {
    return new self($dir, $base, $classFiles);
  }
  
  /**
   * read directory recursivery
   *
   * @param DirectoryIterator $fromElement default null
   * @return $this
   */
  public function read(DirectoryIterator $fromElement = null)
  {
    $elements = (is_null($fromElement)) ? $this->directories : $fromElement;
    
    foreach ($elements as $element) {
      $child = $element->getPathName();
      $entry = ltrim(str_replace($this->dir, '', $child), '/');
      if ($this->isValidDirectory($element)) {
        $this->read(new DirectoryIterator($child));
      } elseif ($this->isValidFile($element)) {
        $this->readClass($entry);
      }
    }
    
    return $this;
  }
  
  protected function readClass($pathToClassWithoutBaseDir)
  {
    // $pathToClassFile has a base directory. such as sabel/core/Context.php
    //                                                ^^^^^
    
    $pathToClassFile = $this->dir . $pathToClassWithoutBaseDir;
    if (strpos($pathToClassFile, '.php') !== false) {
      if (!is_readable($pathToClassFile))
        throw new Exception("$pathToClassFile can't open.");
        
      $classFile = new ClassFile($pathToClassFile);
      $classFile->addLines(explode("\n", file_get_contents($pathToClassFile)));
      $this->files->add($classFile);
    }
  }
  
  protected function isValidDirectory($element)
  {
    return ($element->isDir() && strpos($element->getFileName(), '.') === false);
  }
  
  protected function isValidFile($element)
  {
    return ($element->isFile() && strpos($element->getFileName(), '.') !== 0);
  }
  
  public function write($path)
  {
    $cbuf = $bbuf = $pbuf = $nbuf = array();
    
    $this->files->findChilds();
    $files = $this->files->gets();
    
    foreach ($files as $file) {
      if ($file->hasChild()) {
        $cbuf = array_merge($cbuf, $file->getLines());
      } elseif ($file->hasBoth()) {
        $bbuf = array_merge($bbuf, $file->getLines());
      } elseif ($file->hasParent()) {
        $pbuf = array_merge($pbuf, $file->getLines());
      } elseif ($file->hasnt()) {
        $nbuf = array_merge($nbuf, $file->getLines());
      }
    }
    
    file_put_contents($path, array_merge(array("<?php\n"), $cbuf, $bbuf, $pbuf, $nbuf));
  }
  
  protected function checkParentExists($file, $conflicts)
  {
    $parent = $file->getParent();
    
    if (in_array($parent, $conflicts))    return true;
    if (class_exists($parent, false))     return true;
    if (interface_exists($parent, false)) return true;
    
    $filepath = NameResolver::resolvClassNameToDirectoryPath($parent, false);
    foreach (explode(':', ini_get('include_path')) as $path) {
      if (file_exists($path . '/' . $filepath)) return true;
    }
    
    if (defined('TEST_CASE')) {
      $filepath = NameResolver::resolvClassNameToDirectoryPath($parent);
      foreach (explode(':', ini_get('include_path')) as $path) {
        if (file_exists($path . '/' . $filepath)) return true;
      }
    }
    
    return false;
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

class DirectoryTraverser
{
  protected $dir = '';
  protected $directories = null;
  protected $visitors = array();
  
  public function __construct($dir = null)
  {
    $this->dir = ($dir) ? $dir : dirname(realpath(__FILE__));
    $this->directories = new DirectoryIterator($this->dir);
  }
  
  public function visit($visitor)
  {
    $this->visitors[] = $visitor;
  }
  
  public function traverse(DirectoryIterator $fromElement = null)
  {
    $element = (is_null($fromElement)) ? $this->directories : $fromElement;
    foreach ($element as $e) {
      $child = $e->getPathName();
      $entry = ltrim(str_replace($this->dir, '', $child), '/');
      if ($this->isValidDirectory($e)) {
        foreach ($this->visitors as $visitor) {
          $visitor->accept($entry, 'dir');
        }
        $this->traverse(new DirectoryIterator($child));
      } elseif ($this->isValidFile($e)) {
        foreach ($this->visitors as $visitor) {
          $visitor->accept($entry, 'file', $child);
        }
      }
    }
  }
  
  protected function isValidDirectory($element)
  {
    return ($element->isDir() && strpos($element->getFileName(), '.') === false);
  }
  
  protected function isValidFile($element)
  {
    return ($element->isFile() && strpos($element->getFileName(), '.') !== 0);
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
