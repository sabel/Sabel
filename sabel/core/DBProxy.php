<?php

class Sabel_Core_DBProxy extends Sabel_DB_Mapper implements Iterator
{
  protected $schema   = array();
  protected $columns  = array();
  protected $size     = 0;
  protected $position = 0;
  
  public function __construct($param1 = null, $param2 = null)
  {
    parent::__construct($param1, $param2);
  }
  
  /**
   * implements for Iterator interface
   *
   */
  public function current() {
    return new Sabel_DB_Column($this->schema[$this->position], $this->table);
  }
  
  /**
   * implements for Iterator interface
   *
   */
  public function key()
  {
    return $this->position;
  }
  
  /**
   * implements for Iterator interface
   *
   */
  public function next()
  {
    return $this->position++;
  }
  
  /**
   * implements for Iterator interface
   *
   */
  public function rewind()
  {
    $ischema = new Sabel_DB_Schema_Accessor($this->connectName, 'default');
    $this->columns = $columns = $ischema->getTable($this->table)->getColumns();
    $this->size = count($columns);
    
    $schema = array();
    foreach ($columns as $column) {
      $schema[] = array('name' => $column->name,
                        'data' => $this->data[$column->name]);
    }
    
    $this->schema = $schema;
    $this->position = 0;
  }
  
  /**
   * implements for Iterator interface
   *
   */
  public function valid()
  {
    return ($this->position < $this->size);
  }
}