<?php

if (!defined('DIR_DIVIDER')) define('DIR_DIVIDER', '/');

define('DEFAULT_PHP_POSTFIX', '.php');

final class Sabel
{
  private static $required   = array();
  private static $fileUsing  = array();
  private static $readables  = array();
  private static $singletons = array();
  
  public static function loadState()
  {
    $apc = self::load('Sabel_Cache_Apc');
    self::$readables = $apc->read('readables');
  }
  
  public static function saveState()
  {
    $apc = self::load('Sabel_Cache_Apc');
    $apc->write('readables', self::$readables);
  }
  
  public static function load($className, $constructerArg = null)
  {
    self::using($className);
    
    if (!class_exists($className)) throw new Exception($className ." not found");
    
    if ($constructerArg === null) {
      return new $className();
    } else {
      return new $className($constructerArg);
    }
  }
  
  public static function loadSingleton($className, $constructerArg)
  {
    if (isset(self::$singletons[$className])) {
      $instance = self::$singletons[$className];
    } else {
       self::using($className);
      if ($constructerArg === null) {
        $instance = new $className();
      } else {
        $instance = new $className($constructerArg);
      }
      self::$singletons[$className] = $instance;
    }
    
    return $instance;
  }
  
  public static function using($className)
  {
    if (!isset(self::$required[$className])) {
      $path = self::convertPath($className);
      if (self::isReadable($path)) {
        require ($path);
        self::$required[$className] = true;
      }
    }
  }
  
  public static function fileUsing($path)
  {
    if (!isset(self::$fileUsing[$path])) {
      self::$fileUsing[$path] = true;
      require ($path);
    }
  }
  
  private static function convertPath($className)
  {
    $prePath = str_replace('_', DIR_DIVIDER, $className);
    $path = strtolower(dirname($prePath)) . DIR_DIVIDER 
            . basename($prePath) . DEFAULT_PHP_POSTFIX;
    
    return str_replace('./', '', $path);
  }
  
  private static function isReadable($path)
  {
    if (isset(self::$readables[$path])) return true;
    if (is_readable($path)) return true;
    
    $includePath = get_include_path();
    $paths = explode(':', $includePath);
    
    foreach ($paths as $p) {
      if (is_readable($p .'/'. $path)) {
        self::$readables[$path] = true;
        return true;
      }
    }
    
    return false;
  }
}

/**
 * alias of Sabel::load()
 *
 */
if (function_exists('create')) {
  function __create($className)
  {
    return Sabel::load($className);
  }
} else {
  function create($className)
  {
    return Sabel::load($className);
  }
}