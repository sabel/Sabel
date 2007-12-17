<?php

function add_include_path($path)
{
  $p = RUN_BASE . DS . $path;
  set_include_path(get_include_path() . PATH_SEPARATOR . $p);
}

function add_include_paths($paths)
{
  $path = "";
  foreach ($paths as $p) {
    $path .= RUN_BASE . DS . $p . PATH_SEPARATOR;
  }
  
  set_include_path($path . get_include_path());
}

function load($className, $config)
{
  if (!$config instanceof Sabel_Container_Injection) {
    $msg = var_export($config, 1) . " is not Sabel_Container_Injection";
    throw new Sabel_Exception_Runtime($msg);
  }
  
  return Sabel_Container::create($config)->newInstance($className);
}

function l($message, $level = LOG_INFO, $file = null)
{
  Sabel_Context::log($message, $level, $file);
}

function r($const)
{
  if (class_exists("Redirect")) {
    return ($const === Redirect::REDIRECTED);
  }
}

function redirected($const)
{
  if (class_exists("Redirect")) {
    return ($const === Redirect::REDIRECTED);
  }
}

function realempty($value)
{
  return ($value === null || $value === array() || $value === "");
}

function lcfirst($string)
{
  if (realempty($string) || !is_string($string)) {
    return "";
  } else {
    $string{0} = strtolower($string{0});
    return $string;
  }
}

function reflection()
{
  $args = func_get_args();
  
  if (empty($args)) {
    return;
  } elseif (count($args) === 1) {
    $class = $args[0];
  } else {
    foreach ($args as $arg) {
      reflection($arg);
    }
    return;
  }
  
  echo '<pre style="background: #FFF; color: #333; ' .
       'border: 1px solid #ccc; margin: 5px; padding: 5px;">';
       
  static $files = array();
  
  $ref = new ReflectionClass($class);
  $className = $ref->getName();
  $filePath  = $ref->getFileName();
  
  if (!isset($files[$filePath])) {
    $files[$filePath] = file($filePath);
  }
  
  echo "<b><font size=\"+1\">{$className}</font></b>";
  echo PHP_EOL . PHP_EOL;
  
  if ($methods = $ref->getMethods()) {
    foreach ($methods as $method) {
      $rm = new ReflectionMethod($className, $method->getName());
      $path = $rm->getFileName();
      if (isset($files[$path])) {
        $lines = $files[$path];
      } else {
        $lines = $files[$path] = file($path);
      }
      
      $start = $method->getStartLine();
      $line  = trim($lines[$start - 1]);
      
      if (substr($line, -1, 1) === ",") {
        $line = $line . " " . trim($lines[$start]);
      }
      
      if ($rm->isPublic() && !$rm->isAbstract()) {
        echo "\t<b>{$line}</b>" . PHP_EOL;
      } else {
        echo "\t$line" . PHP_EOL;
      }
    }
  }
  
  echo '</pre>';
}

function dump()
{
  echo '<pre style="background: #FFF; color: #333; ' .
       'border: 1px solid #ccc; margin: 5px; padding: 5px;">';
       
  foreach (func_get_args() as $value) {
    var_dump($value);
  }
  
  echo '</pre>';
}

function candidate($name, $uri, $options = null)
{
  Sabel_Map_Configurator::addCandidate($name, $uri, $options);
}

function environment($string)
{
  switch (strtolower($string)) {
    case "production":  return PRODUCTION;
    case "test":        return TEST;
    case "development": return DEVELOPMENT;
  }
}

function _A($obj)
{
  return new Sabel_Aspect_Proxy($obj);
}

function now()
{
  return date("Y-m-d H:i:s");
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

function is_model($model)
{
  return ($model instanceof Sabel_DB_Abstract_Model);
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
    $path = MODELS_DIR_PATH . DS . $mdlName . PHP_SUFFIX;
    $exists = Sabel::fileUsing($path, true);
  }
  
  $cache[$mdlName] = $exists;
  
  if ($exists) {
    return new $mdlName();
  } else {
    return new Sabel_DB_Model_Proxy($mdlName);
  }
}
