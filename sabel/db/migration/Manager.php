<?php

define("_INT",      Sabel_DB_Type::INT);
define("_SMALLINT", Sabel_DB_Type::SMALLINT);
define("_BIGINT",   Sabel_DB_Type::BIGINT);
define("_FLOAT",    Sabel_DB_Type::FLOAT);
define("_DOUBLE",   Sabel_DB_Type::DOUBLE);
define("_STRING",   Sabel_DB_Type::STRING);
define("_TEXT",     Sabel_DB_Type::TEXT);
define("_DATETIME", Sabel_DB_Type::DATETIME);
define("_DATE",     Sabel_DB_Type::DATE);
define("_BOOL",     Sabel_DB_Type::BOOL);
define("_BYTE",     Sabel_DB_Type::BYTE);
define("_NULL",     "SDB_NULL_VALUE");

/**
 * Sabel_DB_Migration_Manager
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Migration_Manager
{
  private static $accessor  = null;
  private static $driver    = null;
  private static $directory = "";
  private static $applyMode = "";
  private static $start     = null;
  
  public static function setStartVersion($version)
  {
    // @todo
    if (self::$start === null) self::$start = $version;
  }
  
  public static function getStartVersion()
  {
    return self::$start;
  }
  
  public static function clearStartVersion()
  {
    self::$start = null;
  }
  
  public static function setAccessor($accessor)
  {
    self::$accessor = $accessor;
  }
  
  public static function getAccessor()
  {
    return self::$accessor;
  }
  
  public static function setDriver($driver)
  {
    self::$driver = $driver;
  }
  
  public static function getDriver()
  {
    return self::$driver;
  }
  
  public static function setApplyMode($type)
  {
    self::$applyMode = $type;
  }
  
  public static function isUpgrade()
  {
    return (self::$applyMode === "upgrade");
  }
  
  public static function isDowngrade()
  {
    return (self::$applyMode === "downgrade");
  }
  
  public static function setDirectory($dirPath)
  {
    $current = self::$directory;
    self::$directory = $dirPath;

    return $current;
  }
  
  public static function getDirectory()
  {
    return self::$directory;
  }
  
  public static function getFiles($dirPath = null)
  {
    if ($dirPath === null) $dirPath = self::$directory;
    
    if (!is_dir($dirPath)) {
      Sabel_Command::error("no such dirctory. '{$dirPath}'");
      exit;
    }
    
    $files = array();
    foreach (scandir($dirPath) as $file) {
      $num = substr($file, 0, strpos($file, "_"));
      if (!is_numeric($num)) continue;
      
      if (isset($files[$num])) {
        Sabel_Command::error("the same version({$num}) files exists.");
        exit;
      } else {
        $files[$num] = $file;
      }
    }
    
    ksort($files);
    return $files;
  }
}
