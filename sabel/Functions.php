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
  if (is_string($config)) $config = new $config();
  return Sabel_Container::create($config)->newInstance($className);
}

function l($message, $level = LOG_INFO, $fileName = null)
{
  Sabel_Context::log($message, $level, $fileName);
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

function _new($className)
{
  $args = func_get_args();
  unset($args[0]);
  
  if (($c = count($args)) === 0) {
    return new $className();
  } else {
    $code = array();
    for ($i = 1, ++$c; $i < $c; $i++) {
      $code[] = '$args[' . $i. ']';
    }
    eval('$instance = new ' . $className . '(' . implode(", ", $code) . ');');
    return $instance;
  }
}

/***   sabel.db functions   ***/

function is_model($model)
{
  return ($model instanceof Sabel_DB_Abstract_Model);
}

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
