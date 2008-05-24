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
   * @return void
   */
  public static function connect(Sabel_DB_Driver $driver)
  {
    if (Sabel_DB_Transaction::isActive()) {
      $connectionName = $driver->getConnectionName();
      if ($connection = Sabel_DB_Transaction::getConnection($connectionName)) {
        $driver->setConnection($connection);
      } else {
        Sabel_DB_Transaction::begin(self::_connect($driver));
      }
      
      $driver->autoCommit(false);
    } else {
      self::_connect($driver);
    }
  }
  
  /**
   * @param string $connectionName
   *
   * @return void
   */
  public static function close($connectionName)
  {
    if (!isset(self::$connections[$connectionName])) return;
    
    $conn = self::$connections[$connectionName];
    Sabel_DB::createDriver($connectionName)->close($conn);
    
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
  
  /**
   * @param Sabel_DB_Driver $driver
   *
   * @throws Sabel_DB_Exception_Connection
   * @return Sabel_DB_Driver
   */
  protected static function _connect(Sabel_DB_Driver $driver)
  {
    $connectionName = $driver->getConnectionName();
    $names = Sabel_DB_Config::getConnectionNamesOfSameSetting($connectionName);
    
    foreach ($names as $name) {
      if (isset(self::$connections[$name])) {
        $driver->setConnection(self::$connections[$name]);
        return $driver;
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
    
    return $driver;
  }
}
