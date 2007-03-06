<?php

if (!defined('DIR_DIVIDER')) define('DIR_DIVIDER', '/');
define('DEFAULT_PHP_POSTFIX', '.php');
set_include_path(dirname(__FILE__).":".get_include_path());

// regist autoload static method
spl_autoload_register(array('Sabel', 'using'));

/**
 * the core of sabel
 *
 */
final class Sabel
{
  private static $required   = array();
  private static $fileUsing  = array();
  private static $readables  = array();
  private static $singletons = array();
  
  public static function loadState()
  {
    $c = self::load('Sabel_Cache_Manager')->create();
    self::$readables = $c->read('readables');
  }
  
  public static function saveState()
  {
    $c = self::load('Sabel_Cache_Manager')->create();
    $c->write('readables', self::$readables);
  }
  
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

function load($className, $configClass = "Dependency_Config")
{
  return Sabel_Container::load($className, $configClass);
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

function is_not_null($value)
{
  return ($value !== null);
}

function is_not_object($object)
{
  return (!is_object($object));
}

function uri($param, $withDomain = true, $secure = false)
{
  $aCreator = Sabel::loadSingleton('Sabel_View_Uri');
  return $aCreator->uri($param, $withDomain, $secure);
}

function hyperlink($params, $anchor = null, $id = null, $class = null)
{
  $aCreator = Sabel::loadSingleton('Sabel_View_Uri');
  return $aCreator->hyperlink($params, $anchor, $id, $class);
}

function a($param, $anchor, $uriParameters = null, $id = null, $class = null, $secure = false)
{
  $aCreator = Sabel::loadSingleton('Sabel_View_Uri');
  $tag = $aCreator->aTag($param, $anchor, $uriParameters, $id, $class, $secure);
  return $tag;
}

function ah($param, $anchor, $uriParameters = null)
{
  return a($param, htmlspecialchars($anchor), $uriParameters);
}

/**
 * internal request
 */
function request($uri)
{
  $front    = Sabel::loadSingleton('Sabel_Controller_Front');
  $response = $front->ignition(Sabel::load("Sabel_Request_Web", $uri));
  return $response['html'];
}

/**
 * array create utility
 * __(a 10, b 20, c 20) === array("a" => "10", "b" => "20", "c" => "30")
 */
function __($text)
{
  // match (--- or array(---
  preg_match_all('/
    (?:[\s]+|\()
    (?<!array)
    (\((?:[^(]|array\()+\))
    /xU', $text, $arraySource, PREG_SET_ORDER);
  if (count($arraySource) > 0) {
    foreach ($arraySource as $matches) {
      $value = preg_replace('/([^(),\s\'"]+)(,|\))/', "'$1'$2", $matches[1]);
      $text = str_replace($matches[1], 'array'.$value, $text);
    }
    $text = __($text);
    return $text;
  }

  // @todo refactoring this.
  $text = preg_replace('/[\s]*,[\s]+/', ',', $text);
  $text = preg_replace('/(,|array\(|[\s]|^)([^()\s\'",]+)(,| |$)/U', "$1'$2'$3", $text);
  $text = preg_replace('/(,|array\(|[\s]|^)([^()\s\'",]+)(,| |$)/U', "$1'$2'$3", $text);
  $text = preg_replace("/'__(TRUE|FALSE)__'/", '__$1__', $text);
  $text = str_replace("' ", "'=>", $text);
  eval('$array = array('.$text.');');
  return $array;
}

function dump($mixed)
{
  echo '<pre>';
  if (is_array($mixed)) {
    foreach ($mixed as $value) {
      if (is_object($value)) {
        $ref = new ReflectionClass($value);
        $methods = $ref->getMethods();
        echo $ref->getName() . "\n";
        foreach ($methods as $method) {
          echo "\t" . $method->getName() . "\n";
        }
        var_dump($value);
      }
      
      echo "<hr />\n";
    }
  } elseif (is_object($mixed)) {
    $ref = new ReflectionClass($mixed);
    $methods = $ref->getMethods();
    echo $ref->getName() . "\n";
    foreach ($methods as $method) {
      echo "\t" . $method->getName() . "\n";
      echo $method . "\n";
    }
  }
  var_dump($mixed);
  echo '</pre>';
}

function array_ndpop(&$array) {
  $tmp = array_pop($array);
  $array[] = $tmp;
  return $tmp;
}

function candidate($name, $uri, $options = null)
{
  Sabel_Map_Configurator::addCandidate($name, $uri, $options);
}

function add_include_path($path)
{
  set_include_path(RUN_BASE . "{$path}:" . get_include_path());
}

function environment($string)
{
  switch ($string) {
    case 'production':  return PRODUCTION;
    case 'test':        return TEST;
    case 'development': return DEVELOPMENT;
  }
}

if (!extension_loaded('gettext')) {
  function _($val)
  {
    return $val;
  }
}

/**
 * Sabel db functions
 *
 */
function convert_to_tablename($mdlName)
{
  if (preg_match('/^[a-z0-9_]+$/', $mdlName)) return $mdlName;
  return substr(strtolower(preg_replace('/([A-Z])/', '_$1', $mdlName)), 1);
}

function convert_to_modelname($tblName)
{
  return join('', array_map('ucfirst', explode('_', $tblName)));
}

function MODEL($mdlName, $arg1 = null, $arg2 = null)
{
  Sabel_DB_Connection::initialize();
  return Sabel_Model::load($mdlName, $arg1, $arg2);
}

function _A($obj)
{
  return new Sabel_Aspect_Proxy($obj);
}

function renderingComponent($componentName, $args)
{
  echo renderingComponentAsString($componentName, $args);
}

function renderingComponentAsString($componentName, $args)
{
  $path = RUN_BASE . "/components/".$componentName . "/controllers/". $args["controller"] . ".php";
  $cClassName = ucfirst($componentName) . "_Controllers_" . ucfirst($args["controller"]);
  
  if (is_readable($path)) {
    $controller = Sabel::load($cClassName);
    $view = Sabel::load('Sabel_View');
    $view->setTemplatePath(RUN_BASE . "/components/" . $componentName . "/views/");
    $view->setTemplateName($args["controller"] . "." . $args["action"] . ".tpl");
    $controller->setup(Sabel::load("Sabel_Request_Web", ""), $view);
    $controller->execute($args["action"]);
    
    return $view->rendering(false);
  } else {
    throw new Sabel_Exception_Runtime($path . " controller notfound");
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
