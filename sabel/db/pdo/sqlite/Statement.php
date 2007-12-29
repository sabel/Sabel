<?php

/**
 * Sabel_DB_Pdo_Sqlite_Statement
 *
 * @category   DB
 * @package    org.sabel.db.pdo
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Pdo_Sqlite_Statement extends Sabel_DB_Pdo_Statement
{
  public function escape(array $values)
  {
    foreach ($values as &$val) {
      if (is_bool($val)) {
        $val = ($val) ? "true" : "false";
      } elseif (is_object($val)) {
        $val = $this->toSqlValue($val);
      }
    }
    
    return $values;
  }
}
