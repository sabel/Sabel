<?php

class Sabel_DB_Model extends Sabel_DB_Mapper implements Iterator
{
  protected $schema   = array();
  protected $columns  = array();
  protected $size     = 0;
  protected $position = 0;

  protected $loaded   = false;

  public function __construct($param1 = null, $param2 = null)
  {
    parent::__construct($param1, $param2);
  }

  public function __get($key)
  {
    $this->loadSchema();
    return new Sabel_DB_Column($this->schema[$key], $this->columns, $this->table);
  }
  
  protected function loadSchema()
  {
    $this->columns = $columns = $this->getTableSchema()->getColumns();
    $this->size    = count($columns);
    
    $schema = array();
    foreach ($columns as $column) {
      $data = (isset($this->data[$column->name])) ? $this->data[$column->name] : null;
      $values = array('name' => $column->name,
                      'data' => $data);
                      
      $schema[] = $values;
      $schema[$column->name] = $values;
    }
    $this->schema = $schema;
    $this->loaded = true;
  }
  
  /**
   * implements for Iterator interface
   *
   */
  public function current() {
    return new Sabel_DB_Column($this->schema[$this->position], $this->columns, $this->table);
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
    $this->loadSchema();
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
