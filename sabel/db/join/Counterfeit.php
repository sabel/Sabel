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
      $model = MODEL($parent);
      $fKey  = $model->getTableName() . "_" . $model->getPrimaryKey();

      if (($fId = $source->__get($fKey)) === null) {
        throw new Exception("id of foreign key not found.");
      } else {
        $parentModel = $model->selectOne($fId);
        $source->__set($parent, $parentModel);
      }
    }
  }
}
