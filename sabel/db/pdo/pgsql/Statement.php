<?php

/**
 * Sabel_DB_Pdo_Pgsql_Statement
 *
 * @category   DB
 * @package    org.sabel.db.pdo
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Pdo_Pgsql_Statement extends Sabel_DB_Pdo_Statement
{
  public function escape(array $values)
  {
    foreach ($values as &$val) {
      if (is_bool($val)) {
        $val = ($val) ? "t" : "f";
      }
    }
    
    return $values;
  }
  
  public function escapeBinary($string)
  {
    $tmp = addcslashes(str_replace("'", "''", $string), "\000\032\\\r\n");
    return "'" . str_replace(array("\\r", "\\n", "\\"), array("\\015", "\\012", "\\\\"), $tmp) . "'";
  }
  
  public function unescapeBinary($byte)
  {
    return stripcslashes($byte);
  }
}
