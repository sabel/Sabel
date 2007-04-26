<?php

/**
 * Sabel_DB_Join_Counterfeit
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Join_Counterfeit
{
  protected $model = null;

  public function __construct($model)
  {
    $this->model = $model;
  }

  public function setParents($parents)
  {
    $source = $this->model;

    foreach ($parents as $parent) {
      $model   = MODEL($parent);
      $tblName = $model->getTableName();
      $pKey    = $model->getPrimaryKey();

      $fKey = $tblName . "_" . $pKey;
      $fId  = $source->$fKey;

      if ($fId === null) {
        throw new Exception("id of foreign key not found.");
      }

      $parentModel = $model->selectOne($fId);
      $source->$parent = $parentModel;
    }
  }
}
