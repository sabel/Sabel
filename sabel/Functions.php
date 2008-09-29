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

function htmlescape($str, $charset = null)
{
  if ($charset !== null) {
    return htmlentities($str, ENT_QUOTES, $charset);
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
    return htmlentities($str, ENT_QUOTES, $internalEncoding);
  } else {
    return htmlentities($str, ENT_QUOTES);
  }
}

function remove_nullbyte($arg)
{
  if (is_string($arg)) {
    return str_replace("\000", "", $arg);
  } elseif (is_array($arg)) {
    foreach ($arg as &$v) {
      if (is_string($v)) {
        $v = str_replace("\000", "", $v);
      }
    }
    
    return $arg;
  } else {
    return $arg;
  }
}

function get_temp_dir()
{
  static $exists = null;
  
  if ($exists === null) {
    $exists = function_exists("sys_get_temp_dir");
  }
  
  if ($exists) {
    return sys_get_temp_dir();
  } elseif (isset($_ENV["TMP"])) {
    return realpath($_ENV["TMP"]);
  } elseif (isset($_ENV["TMPDIR"])) {
    return realpath($_ENV["TMPDIR"]);
  } elseif (isset($_ENV["TEMP"])) {
    return realpath($_ENV["TEMP"]);
  } else {
    if ($tmpFile = tempnam(md5hash(), "sbl_")) {
      $dirName = realpath(dirname($tmpFile));
      unlink($tmpFile);
      return $dirName;
    } else {
      return null;
    }
  }
}

if (!function_exists("lcfirst")) {
  function lcfirst($str)
  {
    if (!is_string($str) || $str === "") {
      return "";
    } else {
      $str{0} = strtolower($str{0});
      return $str;
    }
  }
}

function now()
{
  return date("Y-m-d H:i:s");
}

function md5hash()
{
  return md5(uniqid(mt_rand(), true));
}

function sha1hash()
{
  return sha1(uniqid(mt_rand(), true));
}

function load($class, $config = null)
{
  static $container = null;
  
  if ($container === null) {
    $container = Sabel_Container::create();
  }
  
  if ($config === null) {
    return $container->load($class);
  } else {
    return $container->load($class, $config);
  }
}

function l($message, $level = SBL_LOG_INFO, $identifier = "default")
{
  Sabel_Logger::create()->write($message, $level, $identifier);
}

function normalize_uri($uri)
{
  $uri = trim(preg_replace("@/{2,}@", "/", $uri), "/");
  $parsedUrl = parse_url("http://localhost/{$uri}");
  return ltrim($parsedUrl["path"], "/");
}

function realempty($value)
{
  return ($value === null || $value === array() || $value === "" || $value === false);
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

function environment($str)
{
  switch (strtolower($str)) {
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

function is_natural_number($num)
{
  if (is_int($num)) {
    return true;
  } elseif (is_string($num)) {
    if ($num === "0") {
      return true;
    } else {
      return (preg_match('/^[1-9][0-9]*$/', $num) === 1);
    }
  } else {
    return false;
  }
}

function preg_match_replace($pattern, $search, $replace, $subject)
{
  if (preg_match_all($pattern, $subject, $matches) > 0) {
    foreach ($matches[0] as $match) {
      $subject = str_replace($match, str_replace($search, $replace, $match), $subject);
    }
  }
  
  return $subject;
}

/***   sabel.db functions   ***/

function is_model($model)
{
  return ($model instanceof Sabel_Db_Model);
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
    $mdlName = implode("", array_map("ucfirst", explode("_", $tblName)));
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
      return new Sabel_Db_Model_Proxy($mdlName, $id);
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
    return new Sabel_Db_Model_Proxy($mdlName, $id);
  }
}

function create_join_key(Sabel_Db_Model $childModel, $parentName)
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
