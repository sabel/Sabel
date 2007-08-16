<?php

/**
 * Sabel_DB_Pdo_Driver_Mysql
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Pdo_Driver_Mysql extends Sabel_DB_Pdo_Driver
{
  public function getDriverId()
  {
    return "pdo-mysql";
  }

  public function escape($values)
  {
    foreach ($values as &$val) {
      if (is_bool($val)) {
        $val = ($val) ? 1 : 0;
      }
    }

    return $values;
  }

  public function getLastInsertId()
  {
    return $this->connection->lastInsertId();
  }
}
