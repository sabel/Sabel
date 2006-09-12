<?php

class Sabel_DB_Basic extends Sabel_DB_Mapper
{
  public function __construct($table = null)
  {
    $this->setDriver('default');
    parent::__construct();

    if (isset($table)) $this->table = $table;
  }
}

abstract class Sabel_DB_Bridge extends Sabel_DB_Mapper
{
  protected $structure   = 'bridge';
  protected $bridgeTable = '';

  public function getChild($child, $table = null)
  {
    $this->enableParent();

    if (is_null($table) && $this->bridgeTable === '')
      throw new Exception('need bridge table name.');

    $table = (is_object($table) || is_null($table)) ? $this->bridgeTable : $table;
    parent::getChild($table);

    $children = array();
    foreach ($this->$table as $bridge) $children[] = $bridge->$child;
    $this->$child = $children;
  }
}

abstract class Sabel_DB_Tree extends Sabel_DB_Mapper
{
  protected $structure = 'tree';

  public function __construct($param1 = null, $param2 = null)
  {
    $this->setDriver('default');
    parent::__construct($param1, $param2);
  }

  public function getRoot()
  {
    return $this->select("{$this->table}_id", 'null');
  }
}
