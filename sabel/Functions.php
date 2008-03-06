<?php

function add_include_path($path)
{
  set_include_path(get_include_path() . PATH_SEPARATOR . $path);
}

function unshift_include_path($path)
{
  set_include_path($path . PATH_SEPARATOR . get_include_path());
}

function unshift_include_paths($paths, $prefix = "")
{
  $path = "";
  foreach ($paths as $p) {
    $path .= $prefix . $p . PATH_SEPARATOR;
  }
  
  set_include_path($path . get_include_path());
}

/*
function load($className, $config)
{
  if (is_string($config)) $config = new $config();
  return Sabel_Container::create($config)->newInstance($className);
}
*/

function l($message, $level = SBL_LOG_INFO, $fileName = null)
{
  Sabel_Logger::create()->write($message, $level, $fileName);
}

function uri($uriParameter, $secure = false, $absolute = false)
{
  if ($secure || $absolute) {
    $protocol  = ($secure) ? "https" : "http";
    $uriPrefix = $protocol . "://" . Sabel_Environment::get("HTTP_HOST");
  } else {
    $uriPrefix = "";
  }
  
  if (defined("URI_IGNORE")) {
    $uriPrefix .= $_SERVER["SCRIPT_NAME"];
  }
  
  $uri = Sabel_Context::getContext()->getCandidate()->uri($uriParameter);
  return $uriPrefix . "/" . $uri;
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
  if (PHP_SAPI === "cli") {
    echo PHP_EOL;
    echo "================================================" . PHP_EOL;
  } else {
    echo '<pre style="background: #FFF; color: #333; ' .
         'border: 1px solid #ccc; margin: 5px; padding: 5px;">';
  }
  
  foreach (func_get_args() as $value) {
    var_dump($value);
  }
  
  if (PHP_SAPI === "cli") {
    echo "================================================" . PHP_EOL;
  } else {
    echo '</pre>';
  }
}

function environment($string)
{
  switch (strtolower($string)) {
    case "production":
      return PRODUCTION;
    case "test":
      return TEST;
    case "development":
      return DEVELOPMENT;
    default:
      return null;
  }
}

function now()
{
  return date("Y-m-d H:i:s");
}

function htmlescape($string, $charset = null)
{
  if ($charset !== null) {
    return htmlentities($string, ENT_QUOTES, $charset);
  }
  
  static $internalEncoding = null;
  
  if ($internalEncoding === null) {
    if (extension_loaded("mbstring")) {
      $internalEncoding = ini_get("mbstring.internal_encoding");
      if ($internalEncoding === "") $internalEncoding = false;
    } else {
      $internalEncoding = false;
    }
  }
  
  if ($internalEncoding) {
    return htmlentities($string, ENT_QUOTES, $internalEncoding);
  } else {
    return htmlentities($string, ENT_QUOTES);
  }
}

/***   sabel.db functions   ***/

function is_model($model)
{
  return ($model instanceof Sabel_DB_Model);
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

function MODEL($mdlName, $id = null)
{
  static $cache = array();
  
  if (isset($cache[$mdlName])) {
    if ($cache[$mdlName]) {
      return new $mdlName($id);
    } else {
      return new Sabel_DB_Model_Proxy($mdlName, $id);
    }
  }
  
  if (!$exists = class_exists($mdlName, false)) {
    $path = MODELS_DIR_PATH . DS . $mdlName . ".php";
    $exists = Sabel::fileUsing($path, true);
  }
  
  $cache[$mdlName] = $exists;
  
  if ($exists) {
    return new $mdlName($id);
  } else {
    return new Sabel_DB_Model_Proxy($mdlName, $id);
  }
}

function create_join_key(Sabel_DB_Model $childModel, $parentName)
{
  if ($fkey = $childModel->getMetadata()->getForeignKey()) {
    foreach ($fkey->toArray() as $colName => $fkey) {
      if ($fkey->table === $parentName) {
        return array("id" => $fkey->column, "fkey" => $colName);
      }
    }
  }
  
  return array("id" => "id", "fkey" => $parentName . "_id");
}
