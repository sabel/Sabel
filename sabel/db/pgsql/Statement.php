<?php

/**
 * Sabel_DB_Pgsql_Statement
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Pgsql_Statement extends Sabel_DB_Abstract_Statement
{
  public function setDriver($driver)
  {
    if ($driver instanceof Sabel_DB_Pgsql_Driver) {
      $this->driver = $driver;
    } else {
      $message = "driver should be an instance of Sabel_DB_Pgsql_Driver";
      throw new Sabel_Exception_InvalidArgument($message);
    }
  }
  
  public function escape(array $values)
  {
    $conn = $this->driver->getConnection();
    
    foreach ($values as &$val) {
      if (is_bool($val)) {
        $val = ($val) ? "'t'" : "'f'";
      } elseif (is_string($val)) {
        $val = "'" . pg_escape_string($conn, $val) . "'";
      } elseif (is_object($val)) {
        $val = $val->getSqlValue($this);
      }
    }
    
    return $values;
  }
}
