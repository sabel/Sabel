<?php

/**
 * Sabel_DB_Schema_Mysql_Factory
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Schema_Mysql_Factory
{
  public static function create($version)
  {
    $exp = explode(".", $version);

    if ($exp[1] === "0") {
      return new Sabel_DB_Schema_Mysql_Mysql50();
    } else {
      return new Sabel_DB_Schema_Mysql_Mysql51();
    }
  }
}
