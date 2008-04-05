<?php

/**
 * Sabel_DB_Pgsql_Statement
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Pgsql_Statement extends Sabel_DB_Statement
{
  public function setDriver($driver)
  {
    if ($driver instanceof Sabel_DB_Pgsql_Driver) {
      $this->driver = $driver;
    } else {
      $message = __METHOD__ . '() $driver should be an instance of Sabel_DB_Pgsql_Driver';
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
      }
    }
    
    return $values;
  }
  
  public function escapeBinary($string)
  {
    $conn = $this->driver->getConnection();
    return "'" . pg_escape_bytea($conn, $string) . "'";
  }
  
  public function unescapeBinary($byte)
  {
    return pg_unescape_bytea($byte);
  }
}
