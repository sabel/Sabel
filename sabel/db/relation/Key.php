<?php

/**
 * Sabel_DB_Relation_Key
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Relation_key
{
  public static function create($model, $keys, $tblName = null)
  {
    if ($tblName === null) {
      $tblName = $model->getTableName();
    }

    $pKey = $model->getPrimaryKey();

    if ($keys === null) $keys = array();

    $fKey = (isset($keys["fKey"])) ? $keys["fKey"] : $tblName . "_" . $pKey;
    $id   = (isset($keys["id"]))   ? $keys["id"]   : $pKey;

    return array("id" => $id, "fKey" => $fKey);
  }
}
