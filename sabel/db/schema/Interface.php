<?php

/**
 * Sabel_DB_Schema_Interface
 *
 * @interface
 * @category   DB
 * @package    org.sabel.db
 * @subpackage schema
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
interface Sabel_DB_Schema_Interface
{
  public function getAll();
  public function get($tblName);
  public function getTableLists();
  public function getColumnNames($tblName);
  public function getTableEngine($tblName);
}
