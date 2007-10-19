<?php

function add_include_path($path)
{
  set_include_path(RUN_BASE . "{$path}:" . get_include_path());
}

function add_include_paths($paths)
{
  $path = "";
  foreach ($paths as $p) $path .= RUN_BASE . DS . $p . ":";
  set_include_path($path . get_include_path());
}

function load($className, $config)
{
  if (!$config instanceof Sabel_Container_Injection) {
    $msg = var_export($config, 1) . " is not Sabel_Container_Injection";
    throw new Sabel_Exception_Runtime($msg);
  }
  
  return Sabel_Container::injector($config)->newInstance($className);
}

function l($message)
{
  Sabel_Context::log($message);
}

function r($const)
{
  return ($const === Redirect::REDIRECTED);
}

function redirected($const)
{
  return ($const === Redirect::REDIRECTED);
}

/**
 * internal request
 */
function request($uri, $destination = null, $request = null, $storage = null)
{
  $context = new Sabel_Context();
  $previousContext = Sabel_Context::getContext();
  Sabel_Context::setContext($context);
  
  if ($request === null) {
    $request = new Sabel_Request_Object();
  }
  
  $builder = new Sabel_Request_Builder();
  $builder->build($request, $uri);
  
  if ($destination === null) {
    $router = new Sabel_Router_Map();
    $destination = $router->route($request, $context);
  }
  $context->setDestination($destination);
  
  if ($storage === null) {
    $storage = new Sabel_Storage_Session();
  }
  
  $context->setStorage($storage);
  
  Sabel_Helper::load($request, $destination);
    
  $plugin = new Sabel_Plugin();
  $plugin->setDestination($destination);
  $context->setPlugin($plugin);
  
  $injector = Sabel_Container::injector(new Config_Factory());
  $context->setInjector($injector);
  $executer = $injector->newInstance(Sabel_Controller_Front::EXECUTER_INTERFACE);
  $executer->setContext($context);
  $executer->setDestination($destination);
  
  $controller = $executer->create();
  $response = $executer->execute($request, $storage, false);
  $response->setController($controller);
  $response->setDestination($destination);
  
  $result = Sabel_View::renderNoLayout($response);
  
  Sabel_Context::setContext($previousContext);
  
  return $result;
}

/**
 * array create utility
 * __(a 10, b 20, c 20) === array("a" => "10", "b" => "20", "c" => "30")
 */
function ___($text)
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
  $text = str_replace("' ", "'=>", $text);
  eval('$array = array('.$text.');');
  return $array;
}


function d($mixed)
{
  echo '<pre style="background: #fff; color: #333;border:1px solid #ccc; margen:2px;padding:3px;font-family:monospace;font-size:12px>"';
  foreach (func_get_args() as $value) var_dump($value);
  echo '</pre>';
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

function environment($string)
{
  switch ($string) {
    case "production":  return PRODUCTION;
    case "test":        return TEST;
    case "development": return DEVELOPMENT;
  }
}

function now()
{
  return date("Y-m-d H:i:s");
}

function _A($obj)
{
  return new Sabel_Aspect_Proxy($obj);
}

function component($action, $args = null)
{
  $context = Sabel_Context::getContext();
  
  $executer    = clone $context->getExecuter();
  $destination = clone $context->getDestination();
  
  if (isset($args["controller"])) {
    $destination->setController($args["controller"]);
  }
  
  if (isset($args["module"])) {
    $destination->setController($args["module"]);
  }
  
  $destination->setAction($action);
  
  $executer->setDestination($destination);
  $controller = $executer->create();
  $response = $executer->execute($context->getRequest(), $context->getStorage());
  $response->setController($controller);
  $response->setDestination($destination);
  
  return Sabel_View::renderDefault($response, false);
}

/***   sabel.db functions   ***/

function convert_to_tablename($mdlName)
{
  static $cache = array();
  
  if (isset($cache[$mdlName])) {
    return $cache[$mdlName];
  }
  
  if (preg_match("/^[a-z0-9_]+$/", $mdlName)) {
    $tblName = $mdlName;
  } else {
    $tblName = substr(strtolower(preg_replace("/([A-Z])/", '_$1', $mdlName)), 1);
  }
  
  return $cache[$mdlName] = $tblName;
}

function convert_to_modelname($tblName)
{
  static $cache = array();
  
  if (isset($cache[$tblName])) {
    return $cache[$tblName];
  } else {
    $mdlName = join("", array_map("ucfirst", explode("_", $tblName)));
    return $cache[$tblName] = $mdlName;
  }
}

function MODEL($mdlName)
{
  static $cache = array();
  
  if (isset($cache[$mdlName])) {
    if ($cache[$mdlName]) {
      return new $mdlName();
    } else {
      return new Sabel_DB_Model_Proxy($mdlName);
    }
  }
  
  if (!$exists = class_exists($mdlName, false)) {
    $exists = Sabel::fileUsing(MODELS_DIR . $mdlName . ".php");
  }
  
  $cache[$mdlName] = $exists;
  
  if ($exists) {
    return new $mdlName();
  } else {
    return new Sabel_DB_Model_Proxy($mdlName);
  }
}
