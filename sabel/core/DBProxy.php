<?php

class Sabel_Core_DBProxy extends Sabel_DB_Mapper implements Iterator
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
    return new Sabel_DB_Column($this->schema[$key], $this->table);
  }
  
  protected function loadSchema()
  {
    if ($this->loaded === false) {
      $ischema = new Sabel_DB_Schema_Accessor($this->connectName, 'default');
      $this->columns = $columns = $ischema->getTable($this->table)->getColumns();
      $this->size = count($columns);
      
      $schema = array();
      foreach ($columns as $column) {
        $values = array('name' => $column->name,
                        'data' => $this->data[$column->name]);
        
        $schema[] = $values;
        $schema[$column->name] = $values;
      }
      
      $this->schema = $schema;
      $this->loaded = true;
    }
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