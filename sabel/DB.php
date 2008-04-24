<?php

/**
 * Sabel_DB
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB
{
  /**
   * @param string $connectionName
   *
   * @throws Sabel_Exception_ClassNotFound
   * @return Sabel_DB_Driver
   */
  public static function createDriver($connectionName = "default")
  {
    $className = self::classPrefix($connectionName) . "Driver";
    
    Sabel::using($className);
    
    if (class_exists($className, false)) {
      $driver = new $className($connectionName);
    } elseif ($baseClass = self::getBaseClassName($connectionName, "Driver")) {
      $driver = new $baseClass($connectionName);
    } else {
      $message = __METHOD__ . "() Class '{$className}' not Found.";
      throw new Sabel_Exception_ClassNotFound($message);
    }
    
    Sabel_DB_Connection::connect($driver);
    
    return $driver;
  }
  
  /**
   * @param string $connectionName
   *
   * @throws Sabel_Exception_ClassNotFound
   * @return Sabel_DB_Statement
   */
  public static function createStatement($connectionName = "default")
  {
    $className = self::classPrefix($connectionName) . "Statement";
    $driver = self::createDriver($connectionName);

    Sabel::using($className);
    
    if (class_exists($className, false)) {
      $statement = new $className($driver);
    } elseif ($baseClass = self::getBaseClassName($connectionName, "Statement")) {
      $statement = new $baseClass($driver);
    } else {
      $message = __METHOD__ . "() Class '{$className}' not Found.";
      throw new Sabel_Exception_ClassNotFound($message);
    }
    
    return $statement;
  }
  
  /**
   * @param string $connectionName
   *
   * @throws Sabel_Exception_ClassNotFound
   * @return Sabel_DB_Abstract_Metadata
   */
  public static function createMetadata($connectionName = "default")
  {
    $className  = self::classPrefix($connectionName) . "Metadata";
    $schemaName = Sabel_DB_Config::getSchemaName($connectionName);
    
    Sabel::using($className);
    
    if (class_exists($className, false)) {
      return new $className(self::createDriver($connectionName), $schemaName);
    } elseif ($baseClass = self::getBaseClassName($connectionName, "Metadata")) {
      return new $baseClass(self::createDriver($connectionName), $schemaName);
    } else {
      $message = __METHOD__ . "() Class '{$className}' not Found.";
      throw new Sabel_Exception_ClassNotFound($message);
    }
  }
  
  /**
   * @param string $connectionName
   *
   * @throws Sabel_Exception_ClassNotFound
   * @return Sabel_DB_Abstract_Migration
   */
  public static function createMigration($connectionName = "default")
  {
    $className = self::classPrefix($connectionName) . "Migration";
    
    Sabel::using($className);
    
    if (class_exists($className, false)) {
      return new $className();
    } elseif ($baseClass = self::getBaseClassName($connectionName, "Migration")) {
      return new $baseClass();
    } else {
      $message = __METHOD__ . "() Class '{$className}' not Found.";
      throw new Sabel_Exception_ClassNotFound($message);
    }
  }
  
  /**
   * @param string $connectionName
   *
   * @return string
   */
  private static function classPrefix($connectionName)
  {
    $dirs = explode(".", Sabel_DB_Config::getPackage($connectionName));
    return implode("_", array_map("ucfirst", $dirs)) . "_";
  }
  
  /**
   * @param string $connectionName
   * @param string $className
   *
   * @return mixed
   */
  protected static function getBaseClassName($connectionName, $className)
  {
    $packageName = Sabel_DB_Config::getPackage($connectionName);
    $reserved = array("mysql", "pgsql", "oci", "ibase");
    
    foreach ($reserved as $part) {
      if (strpos($packageName, $part) !== false) {
        return "Sabel_DB_" . ucfirst($part) . "_" . $className;
      }
    }
    
    return false;
  }
}
