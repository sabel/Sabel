<?php

/**
 * Sabel_DB_Schema_Common
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @subpackage schema
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Schema_Common
{
  protected $recordObj = null;

  public function getTable($tblName)
  {
    $cache  = Sabel_DB_SimpleCache::get('schema_' . $tblName);
    if ($cache) return $cache;

    $sClass = get_schema_by_tablename($tblName);

    if ($sClass) {
      $cols = array();
      foreach ($sClass->get() as $colName => $colInfo) {
        $co = new Sabel_DB_Schema_Column();
        $co->name = $colName;
        $cols[$colName] = $co->make($colInfo);
      }
    } else {
      $cols = $this->createColumns($tblName);
    }

    $schema = new Sabel_DB_Schema_Table($tblName, $cols);
    Sabel_DB_SimpleCache::add('schema_' . $tblName, $schema);
    return $schema;
  }

  protected abstract function createColumns($tblName);
}
