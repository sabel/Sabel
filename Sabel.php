<?php

if (!defined('DIR_DIVIDER')) define('DIR_DIVIDER', '/');
define('DEFAULT_PHP_POSTFIX', '.php');
set_include_path(dirname(__FILE__).":".get_include_path());

// regist autoload static method
spl_autoload_register(array('Sabel', 'using'));

require ("sabel/Functions.php");

/**
 * the core of sabel
 *
 */
final class Sabel
{
  private static $required   = array();
  private static $fileUsing  = array();
  private static $singletons = array();
    
  /**
   * class instanciate.
   * if not using class then using
   *
   * @param string $className class name to using and instanciate
   * @param mixed $arg
   * @param mixed $args
   * @return object
   */
  public static function load($className)
  {
    $arg_list = '';

    if (($numargs = func_num_args()) > 1) {
      $args = func_get_args();
      $arg_list = array();
      
      for ($i = 1; $i < $numargs; $i++) {
        $arg_list[] = '$args[' . $i . ']';
      }
      $arg_list = join(', ', $arg_list);
    }
    
    escapeshellcmd($className);
    escapeshellcmd($arg_list);
    eval ('$instance = new ' . $className . '(' . $arg_list . ');');
    
    return $instance;
  }
  
  public static function loadSingleton($className, $constructerArg = null)
  {
    if (!isset(self::$singletons[$className]))
      self::$singletons[$className] = self::load($className, $constructerArg);
      
    $instance = self::$singletons[$className];
    
    return $instance;
  }
  
  public static function using($className)
  {
    if (!isset(self::$required[$className]) && !class_exists($className, true)) {
      $path = self::convertPath($className);
      if (self::isReadable($path)) {
        require ($path);
        self::$required[$className] = true;
      }
    }
  }
  
  public static function fileUsing($path, $once = false)
  {
    if (!isset(self::$fileUsing[$path])) {
      if (!is_readable($path)) throw new Exception("file not found");
      if ($once) {
        require_once ($path);
      } else {
        require ($path);
      }
      self::$fileUsing[$path] = true;
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
    if (is_readable($path)) return true;
    
    $includePath = get_include_path();
    $paths = explode(':', $includePath);
    
    foreach ($paths as $p) {
      if (is_readable($p .'/'. $path)) {
        return true;
      }
    }
    
    return false;
  }
}

/**
 * Sabel constant values
 *
 */
class Sabel_Const
{
  /**
   * controllers directory.
   */
  const CONTROLLER_DIR = '/controllers/';

  /**
   * postfix extention of controller class.
   */
  const CONTROLLER_POSTFIX = '.php';

  /**
   * modules directory
   */
  const MODULES_DIR = '/app/';

  /**
   * common files of project
   */
  const COMMONS_DIR = 'app/commons/';

  /**
   * templates dirctory
   */
  const TEMPLATE_DIR = 'views/';

  /**
   * postfix extention for template
   */
  const TEMPLATE_POSTFIX = '.tpl';

  /**
   * separater of template
   */
  const TEMPLATE_NAME_SEPARATOR = '.';

  /**
   * modules default name
   */
  const DEFAULT_MODULE = 'Index';

  /**
   * controllers default name
   */
  const DEFAULT_CONTROLLER = 'index';

  /**
   * default action method
   */
  const DEFAULT_ACTION = 'index';
  
  const DEFAULT_LAYOUT = 'layout.tpl';
}
