<?php

/**
 * Sabel_DB_Join_Base
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Join_Base
{
  protected $objects = array();

  protected $tblName       = "";
  protected $sourceModel   = null;
  protected $resultBuilder = null;

  public function __construct($model)
  {
    $this->sourceModel   = $model;
    $this->tblName       = $model->getTableName();
    $this->resultBuilder = Sabel_DB_Join_Result::getInstance();
  }

  public function add($object, $columns = null, $alias = null, $joinKeys = null)
  {
    $fkeys  = $this->sourceModel->getSchema()->getForeignKeys();
    $object = new Sabel_DB_Join_Object($object, $fkeys, $joinKeys, $columns, $alias);

    $this->objects[] = $object;

    $builder = $this->resultBuilder;
    $builder->setObject($object);
    $builder->addStructure($this->tblName, $object->getName());

    Sabel_DB_Join_Alias::regist($this->tblName, $object);

    if ($alias !== null) {
      $name = $object->getModel()->getTableName();
      Sabel_DB_Join_Alias::change($name, $alias, $object);
    }

    return $this;
  }
}
