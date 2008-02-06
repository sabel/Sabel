<?php

/**
 * Sabel_DB_Connection
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Connection
{
  /**
   * @var array
   */
  private static $connections = array();
  
  /**
   * @param Sabel_DB_Abstract_Driver $driver
   *
   * @throws Sabel_DB_Connection_Exception
   * @return resource
   */
  public static function connect(Sabel_DB_Abstract_Driver $driver)
  {
    $connectionName = $driver->getConnectionName();
    
    if (!isset(self::$connections[$connectionName])) {
      $currentLevel = error_reporting(0);
      $result = $driver->connect(Sabel_DB_Config::get($connectionName));
      error_reporting($currentLevel);
      
      if (is_string($result)) {
        throw new Sabel_DB_Connection_Exception($result);
      } else {
        self::$connections[$connectionName] = $result;
      }
    }
    
    $driver->setConnection(self::$connections[$connectionName]);
    return self::$connections[$connectionName];
  }
  
  /**
   * @param string $connectionName
   *
   * @return void
   */
  public static function close($connectionName)
  {
    if (!isset(self::$connections[$connectionName])) return;
    
    $conn   = self::$connections[$connectionName];
    $driver = Sabel_DB_Package::getDriver($connectionName);
    $driver->close($conn);
    
    unset(self::$connections[$connectionName]);
  }
  
  /**
   * @return void
   */
  public static function closeAll()
  {
    foreach (Sabel_DB_Config::get() as $connectionName => $config) {
      self::close($connectionName);
    }
    
    self::$connections = array();
  }
}
