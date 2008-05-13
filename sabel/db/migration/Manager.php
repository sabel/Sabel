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
define("_BINARY",   Sabel_DB_Type::BINARY);
define("_NULL",     "SDB_NULL_VALUE");

/**
 * Sabel_DB_Migration_Manager
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Migration_Manager
{
  private static $metadata  = null;
  private static $stmt      = null;
  private static $directory = "";
  private static $applyMode = "";
  
  public static function setMetadata(Sabel_DB_Abstract_Metadata $metadata)
  {
    self::$metadata = $metadata;
  }
  
  public static function getMetadata()
  {
    return self::$metadata;
  }
  
  public static function setStatement(Sabel_DB_Statement $stmt)
  {
    self::$stmt = $stmt;
  }
  
  public static function getStatement()
  {
    self::$stmt->clear();
    return self::$stmt;
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
  
  public static function setDirectory($directory)
  {
    self::$directory = $directory;
  }
  
  public static function getDirectory()
  {
    return self::$directory;
  }
  
  public static function getFiles()
  {
    if (!is_dir(self::$directory)) {
      Sabel_Console::error("no such dirctory. '" . self::$directory . "'");
      exit;
    }
    
    $files = array();
    foreach (scandir(self::$directory) as $file) {
      $num = substr($file, 0, strpos($file, "_"));
      if (!is_numeric($num)) continue;
      
      if (isset($files[$num])) {
        Sabel_Console::error("the same version({$num}) files exists.");
        exit;
      } else {
        $files[$num] = $file;
      }
    }
    
    ksort($files);
    return $files;
  }
}
