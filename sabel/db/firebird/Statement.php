<?php

/**
 * Sabel_DB_Firebird_Statement
 *
 * @category   DB
 * @package    org.sabel.db
 * @subpackage firebird
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Firebird_Statement extends Sabel_DB_General_Statement
{
  protected function makeLimitationSQL(&$sql, $limit, $offset, $order)
  {
    $tmp = substr(join('', $sql), 6);

    if (isset($limit)) {
      $query  = "FIRST $limit ";
      $query .= (isset($offset)) ? 'SKIP ' . $offset : 'SKIP 0';
      $sql    = array('SELECT ' . $query . $tmp);
    } else {
      if (isset($offset)) $sql = array('SELECT SKIP ' . $offset . $tmp);
    }
  }
}
