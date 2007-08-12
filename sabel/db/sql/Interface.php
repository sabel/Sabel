<?php

/**
 * Sabel_DB_Sql_Interface
 *
 * @interface
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
interface Sabel_DB_Sql_Interface
{
  public static function buildSelectSql($tblName, $projection);
  public static function buildInsertSql($tblName, $values);
  public static function buildUpdateSql($tblName, $values);
}
