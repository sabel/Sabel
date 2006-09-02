<?php

/**
 * Sabel_DB_Schema_Accessor
 *
 * @package org.sabel.db.schema
 * @author Ebine Yutaka <ebine.yutaka@gamil.com>
 */
class Sabel_DB_Schema_Accessor
{
  protected $is = null;

  public function __construct($connectName, $schema)
  {
    $dbName    = ucfirst(Sabel_DB_Connection::getDB($connectName));
    $className = "Sabel_DB_Schema_{$dbName}";
    $this->is  = new $className($connectName, $schema);
  }

  public function getTables()
  {
    return $this->is->getTables();
  }

  public function getTable($name)
  {
    return $this->is->getTable($name);
  }

  public function getColumn($table, $column)
  {
    return $this->is->createColumn($table, $column);
  }
}

/**
 * public function. schema()
 *
 * add schema information to a value of a model and return it.
 */
function schema($model)
{
  if ($model instanceof Sabel_DB_Mapper) {
    $sa = new Sabel_DB_Schema_Accessor($model->getConnectName(), $model->getSchemaName());
    $columns = $sa->getTable($model->getTableName())->getColumns();

    $data = $model->toArray();
    foreach ($data as $key => $val)
      if (array_key_exists($key, $columns)) $columns[$key]->value = $val;

    return $columns;
  } else {
    throw new Exception('invalid instance. schema() need instance of Sabel_DB_Mapper.');
  }
}
