<?php

/**
 * Sabel_DB_Mysqli_Statement
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Mysqli_Statement extends Sabel_DB_Mysql_Statement
{
  public function setDriver($driver)
  {
    if ($driver instanceof Sabel_DB_Mysqli_Driver) {
      $this->driver = $driver;
    } else {
      $message = "driver should be an instance of Sabel_DB_Mysqli_Driver";
      throw new Sabel_Exception_InvalidArgument($message);
    }
  }
  
  public function escape(array $values)
  {
    $conn = $this->driver->getConnection();
    
    foreach ($values as &$val) {
      if (is_bool($val)) {
        $val = ($val) ? 1 : 0;
      } elseif (is_string($val)) {
        $val = "'" . mysqli_real_escape_string($conn, $val) . "'";
      }
    }
    
    return $values;
  }
  
  public function escapeBinary($string)
  {
    $conn = $this->driver->getConnection();
    return "'" . mysqli_real_escape_string($conn, $string) . "'";
  }
  
  public function unescapeBinary($byte)
  {
    return $byte;
  }
}
