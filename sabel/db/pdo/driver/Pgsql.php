<?php

/**
 * Sabel_DB_Pdo_Driver_Pgsql
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Pdo_Driver_Pgsql extends Sabel_DB_Pdo_Driver
{
  public function getDriverId()
  {
    return "pdo-pgsql";
  }

  public function escape($values)
  {
    foreach ($values as &$val) {
      if (is_bool($val)) {
        $val = ($val) ? "t" : "f";
      }
    }

    return $values;
  }

  public function getLastInsertId()
  {
    $stmt = Sabel_DB_Statement::create($this, Sabel_DB_Statement::SELECT);
    $rows = $stmt->setSql("SELECT LASTVAL() AS id")->execute();
    return $rows[0]["id"];
  }
}
