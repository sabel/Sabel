<?php

/**
 * Sabel_DB_Sql
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Sql implements Sabel_DB_Sql_Interface
{
  public static function buildSelectSql($tblName, $projection)
  {
    return "SELECT $projection FROM $tblName";
  }

  public static function buildInsertSql($tblName, $values)
  {
    self::emptyCheck($values, "insert");

    $binds = array();
    $data  = (isset($values[0])) ? $values[0] : $values;
    $keys  = array_keys($data);

    foreach ($keys as $key) $binds[] = ":" . $key;

    $sql = array("INSERT INTO $tblName (");
    $sql[] = join(", ", $keys);
    $sql[] = ") VALUES(";
    $sql[] = join(", ", $binds);
    $sql[] = ")";

    $sql = implode("", $sql);

    if (isset($values[0])) {
      $sqls = array();
      foreach ($values as $vals) $sqls[] = $sql;
      $sql = $sqls;
    }

    return $sql;
  }

  public static function buildUpdateSql($tblName, $values)
  {
    self::emptyCheck($values, "update");

    foreach ($values as $column => $value) {
      $sql[] = "$column = :{$column}";
    }

    return "UPDATE $tblName SET " . implode(", ", $sql);
  }

  private static function emptyCheck($values, $method)
  {
    if (empty($values)) {
      $message = "build" . ucfirst($method) . "Sql() empty $method values.";
      throw new Sabel_DB_Exception($message);
    } else {
      return true;
    }
  }
}
