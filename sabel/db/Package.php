<?php

/**
 * Package Manager for sabel.db
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Package
{
  /**
   * @param string $connectionName
   *
   * @return Sabel_DB_Abstract_Driver
   */
  public static function getDriver($connectionName = "default")
  {
    $className = self::classPrefix($connectionName) . "Driver";
    $driver = new $className($connectionName);
    
    if (Sabel_DB_Transaction::isActive()) {
      $connection = Sabel_DB_Transaction::getConnection($connectionName);
      if ($connection === null) {
        Sabel_DB_Connection::connect($driver);
        Sabel_DB_Transaction::begin($driver);
      } else {
        $driver->setConnection($connection);
        $driver->autoCommit(false);
      }
    } else {
      Sabel_DB_Connection::connect($driver);
    }
    
    return $driver;
  }
  
  /**
   * @param string $connectionName
   *
   * @return Sabel_DB_Abstract_Statement
   */
  public static function getStatement($connectionName = "default")
  {
    $className = self::classPrefix($connectionName) . "Statement";
    $statement = new $className();
    $statement->setDriver(self::getDriver($connectionName));
    
    return $statement;
  }
  
  /**
   * @param string $connectionName
   *
   * @return Sabel_DB_Abstract_Schema
   */
  public static function getSchema($connectionName = "default")
  {
    $className  = self::classPrefix($connectionName) . "Schema";
    $schemaName = Sabel_DB_Config::getSchemaName($connectionName);
    return new $className(self::getDriver($connectionName), $schemaName);
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
}
