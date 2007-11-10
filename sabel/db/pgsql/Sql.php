<?php

/**
 * Sabel_DB_Pgsql_Sql
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Pgsql_Sql extends Sabel_DB_Abstract_Sql
{
  public function escape(array $values)
  {
    $conn = $this->driver->getConnection();

    foreach ($values as &$val) {
      if (is_bool($val)) {
        $val = ($val) ? "'t'" : "'f'";
      } elseif (is_object($val)) {
        $val = $this->escapeObject($val);
      } elseif (is_string($val)) {
        $val = "'" . pg_escape_string($conn, $val) . "'";
      }
    }

    return $values;
  }
}
