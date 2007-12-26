<?php

/**
 * Sabel_DB_Config
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Config
{
  private static $initialized = false;
  private static $configs = array();
  
  public static function initialize($configPath)
  {
    if (self::$initialized) return;
    
    if (is_file($configPath)) {
      Sabel::fileUsing($configPath, true);
      foreach (get_db_params() as $connectionName => $params) {
        self::add($connectionName, $params);
      }
      self::$initialized = true;
    } else {
      throw new Sabel_Exception_FileNotFound($configPath);
    }
  }
  
  public static function add($connectionName, $params)
  {
    self::$configs[$connectionName] = $params;
  }
  
  public static function get($connectionName = null)
  {
    if ($connectionName === null) {
      return self::$configs;
    } else {
      return self::getConfig($connectionName);
    }
  }
  
  public static function getPackage($connectionName)
  {
    $config = self::getConfig($connectionName);
    
    if (isset($config["package"])) {
      return $config["package"];
    } else {
      $message = "'package' not found in config.";
      throw new Sabel_DB_Exception($message);
    }
  }
  
  public static function getSchemaName($connectionName)
  {
    $package = self::getPackage($connectionName);
    $ignores = array("sabel.db.pdo.sqlite" => 1, "sabel.db.ibase" => 1);
    if (isset($ignores[$package])) return null;
    
    $config  = self::getConfig($connectionName);
    $ignores = array("sabel.db.mysql"     => 1,
                     "sabel.db.mysqli"    => 1,
                     "sabel.db.pdo.mysql" => 1,
                     "sabel.db.mssql"     => 1);
                     
    if (isset($ignores[$package])) {
      return $config["database"];
    } elseif ($package === "sabel.db.oci") {
      return strtoupper($config["user"]);
    } elseif (isset($config["schema"])) {
      return $config["schema"];
    } elseif ($package === "sabel.db.pgsql" || $package === "sabel.db.pdo.pgsql") {
      return "public";
    }
    
    $message = "getSchemaName() 'schema' not found in config.";
    throw new Sabel_DB_Exception($message);
  }
  
  private static function getConfig($connectionName)
  {
    if (isset(self::$configs[$connectionName])) {
      return self::$configs[$connectionName];
    } else {
      $message = "getConfig() config for '{$connectionName}' not found.";
      throw new Sabel_DB_Exception($message);
    }
  }
}
