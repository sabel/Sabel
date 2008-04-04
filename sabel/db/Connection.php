<?php

/**
 * Sabel_DB_Connection
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Connection
{
  /**
   * @var resource[]
   */
  private static $connections = array();
  
  /**
   * @param Sabel_DB_Driver $driver
   *
   * @throws Sabel_DB_Exception_Connection
   * @return resource
   */
  public static function connect(Sabel_DB_Driver $driver)
  {
    $connectionName = $driver->getConnectionName();
    $names = Sabel_DB_Config::getConnectionNamesOfSameSetting($connectionName);
    
    foreach ($names as $name) {
      if (isset(self::$connections[$name])) {
        $driver->setConnection(self::$connections[$name]);
        return self::$connections[$name];
      }
    }
    
    if (!isset(self::$connections[$connectionName])) {
      $result = $driver->connect(Sabel_DB_Config::get($connectionName));
      
      if (is_string($result)) {
        throw new Sabel_DB_Exception_Connection($result);
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
    $driver = Sabel_DB::createDriver($connectionName);
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
