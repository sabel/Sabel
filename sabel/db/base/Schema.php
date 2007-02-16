<?php

Sabel::using('Sabel_ValueObject');
Sabel::using('Sabel_DB_SimpleCache');
Sabel::using('Sabel_DB_Schema_Table');

/**
 * Sabel_DB_Base_Schema
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @subpackage base
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Base_Schema
{
  protected $driver = null;

  public function getTable($tblName)
  {
    $mdlName  = convert_to_modelname($tblName);
    $sClsName = 'Schema_' . $mdlName;

    Sabel::using($sClsName);
    if (class_exists($sClsName, false)) {
      $cols = array();
      $sCls = new $sClsName();
      foreach ($sCls->get() as $colName => $colInfo) {
        $colInfo['name'] = $colName;
        $cols[$colName]  = new Sabel_ValueObject($colInfo);
      }
    } else {
      $cols = $this->createColumns($tblName);
    }

    return new Sabel_DB_Schema_Table($tblName, $cols);
  }

  public function getTableEngine($tblName)
  {
    return null;
  }

  protected abstract function getTableNames();
  protected abstract function getTables();
  protected abstract function createColumns($tblName);
}
