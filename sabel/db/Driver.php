<?php

/**
 * Sabel_DB_Driver
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Driver
{
  public static function create($connectionName = "default")
  {
    $driverName = Sabel_DB_Config::getDriverName($connectionName);
    
    if (substr($driverName, 0, 3) === "pdo") {
      list (, $db) = explode("-", $driverName);
      $className = "Sabel_DB_Pdo_Driver_" . ucfirst($db);
    } else {
      $className = "Sabel_DB_" . ucfirst($driverName) . "_Driver";
    }
    
    $driver = new $className($connectionName);
    
    if (Sabel_DB_Transaction::isActive()) {
      $connection = Sabel_DB_Transaction::getConnection($connectionName);
      if ($connection === null) {
        Sabel_DB_Transaction::begin($driver);
      } else {
        $driver->setConnection($connection);
        $driver->autoCommit(false);
      }
    }
    
    return $driver;
  }
  
  public static function createStatement($connectionName = "default")
  {
    $driverName = Sabel_DB_Config::getDriverName($connectionName);
    
    if (substr($driverName, 0, 3) === "pdo") {
      $className = "Sabel_DB_Pdo_Statement";
    } else {
      $className  = "Sabel_DB_" . ucfirst($driverName) . "_Statement";
    }
    
    return new $className(self::create($connectionName));
  }
}
