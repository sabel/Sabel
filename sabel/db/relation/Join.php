<?php

/**
 * Sabel_DB_Relation_Join
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Relation_Join
{
  protected $joins   = array();
  protected $objects = array();

  protected $sourceModel   = null;
  protected $tblName       = "";
  protected $resultBuilder = null;

  public function __construct($model)
  {
    $this->sourceModel   = $model;
    $this->tblName       = $model->getTableName();
    $this->resultBuilder = Sabel_DB_Relation_Join_Result::getInstance();
  }

  public function getSourceModel()
  {
    return $this->sourceModel;
  }

  public function add($object, $joinKeys = null, $columns = null, $alias = null)
  {
    $object = new Sabel_DB_Relation_Join_Object($object, $joinKeys, $columns, $alias);
    $this->objects[] = $object;

    $builder = $this->resultBuilder;
    $builder->setObject($object);
    $builder->addStructure($this->tblName, $object->getName());

    Sabel_DB_Relation_Join_Alias::regist($this->tblName, $object);

    if ($alias !== null) {
      $name = $object->getModel()->getTableName();
      Sabel_DB_Relation_Join_Alias::change($name, $alias, $object);
    }

    return $this;
  }

  public function getObjects()
  {
    return $this->objects;
  }
}
