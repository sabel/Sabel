<?php

/**
 * Sabel_DB_Join_Relation
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Join_Relation extends Sabel_DB_Join_Base
{
  public function __construct($model)
  {
    $this->sourceModel   = $model;
    $this->tblName       = $model->getTableName();
    $this->resultBuilder = Sabel_DB_Join_Result::getInstance();
  }

  public function getSourceModel()
  {
    return $this->sourceModel;
  }

  public function getObjects()
  {
    return $this->objects;
  }
}
