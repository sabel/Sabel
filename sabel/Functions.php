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

if (extension_loaded("mbstring")) {
  function htmlescape($str, $charset = null)
  {
    static $ienc = null;
    
    if ($charset === null) {
      if ($ienc === null) {
        $ienc = mb_internal_encoding();
      }
      
      $charset = $ienc;
    }
    
    return htmlentities($str, ENT_QUOTES, $ienc);
  }
  
  function xmlescape($str, $charset = null) {
    static $ienc = null;
    
    if ($charset === null) {
      if ($ienc === null) {
        $ienc = mb_internal_encoding();
      }
      
      $charset = $ienc;
    }
    
    return str_replace("&#039;", "&apos;", htmlspecialchars($str, ENT_QUOTES, $charset));
  }
} else {
  function htmlescape($str, $charset = null)
  {
    return htmlentities($str, ENT_QUOTES);
  }
  
  function xmlescape($str, $charset = null) {
    return str_replace("&#039;", "&apos;", htmlspecialchars($str, ENT_QUOTES));
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
  return is_empty($value);
}

function is_empty($value)
{
  return ($value === null || $value === "" || $value === array() || $value === false);
}

function dump()
{
  if (is_cli()) {
    echo PHP_EOL;
    echo "================================================" . PHP_EOL;
  } else {
    echo '<pre style="background: #FFF; color: #333; ' .
         'border: 1px solid #ccc; margin: 5px; padding: 5px;">';
  }
  
  foreach (func_get_args() as $value) {
    var_dump($value);
  }
  
  if (is_cli()) {
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

function is_cli()
{
  return (PHP_SAPI === "cli");
}

function is_ipaddr($arg)
{
  if (is_string($arg)) {
    $ptn = "(0|[1-9][0-9]{0,2})";
    if (preg_match("/^{$ptn}\.{$ptn}\.{$ptn}\.{$ptn}$/", $arg) === 1) {
      foreach (explode(".", $arg) as $part) {
        if ($part > 255) return false;
      }
      
      return true;
    } else {
      return false;
    }
  } else {
    return false;
  }
}

function is_number($num)
{
  if (is_int($num)) {
    return true;
  } elseif (is_string($num)) {
    return (preg_match('/^\-?[1-9][0-9]*$/', $num) === 1);
  } else {
    return false;
  }
}

function is_natural_number($num)
{
  if (is_int($num)) {
    return ($num >= 0);
  } elseif (is_string($num)) {
    return ($num === "0" || preg_match('/^[1-9][0-9]*$/', $num) === 1);
  } else {
    return false;
  }
}

function strtoint($str)
{
  if (is_int($str)) {
    return $str;
  } elseif (!is_string($str) || is_empty($str)) {
    return 0;
  }
  
  $len  = strlen($str);
  $char = strtolower($str{$len - 1});
  
  if (in_array($char, array("k", "m", "g"), true)) {
    $num = substr($str, 0, $len - 1);
    if (is_number($num)) {
      switch ($char) {
        case "k": return $num * 1024;
        case "m": return $num * pow(1024, 2);
        case "g": return $num * pow(1024, 3);
        default : return 0;
      }
    } else {
      return 0;
    }
  } else {
    return (is_number($str)) ? (int)$str : 0;
  }
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

function finder($mdlName, $projection = null)
{
  return new Sabel_Db_Finder($mdlName, $projection);
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
