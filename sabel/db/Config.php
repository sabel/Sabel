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
  
  public static function initialize(Sabel_Config $config)
  {
    if (self::$initialized) return;
    
    foreach ($config->configure() as $connectionName => $params) {
      self::$configs[$connectionName] = $params;
    }
    
    self::$initialized = true;
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
    
    $config = self::getConfig($connectionName);
    
    // @todo more improvement
    
    if (isset($config["schema"])) {
      return $config["schema"];
    } elseif (strpos($package, "mysql") !== false || $package === "sabel.db.mssql") {
      return $config["database"];
    } elseif (strpos($package, "pgsql") !== false) {
      return "public";
    } elseif (strpos($package, "oci") !== false) {
      return strtoupper($config["user"]);
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
