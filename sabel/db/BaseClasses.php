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
  protected $structure = 'bridge';

  public function __construct($param1 = null, $param2 = null)
  {
    $this->setDriver('default');
    parent::__construct($param1, $param2);
  }

  public function getChild($child, $obj = null)
  {
    $this->enableParent();
    parent::getChild($obj);

    $children = array();
    foreach ($this->$obj as $bridge) $children[] = $bridge->$child;
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
