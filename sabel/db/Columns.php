<?php

/**
 * Sabel_DB_Columns for access to information schema.
 * 
 * @package org.sabel.db
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_DB_Columns implements Iterator
{
  protected $data     = array();
  protected $size     = 0;
  protected $position = 0;
  protected $table    = '';
  protected $columns  = array();
  
  public function __construct($data, $connectionName, $table)
  {
    $this->data  = $data;
    $this->table = $table;
    
    $ischema = new Sabel_DB_Schema_Accessor($connectionName, 'default');
    $this->columns = $columns = $ischema->getTable($table)->getColumns();
    $this->size = count($columns);
    
    $schema = array();
    foreach ($columns as $column) {
      $schema[] = array('name' => $column->name,
                        'data' => $data[$column->name]);
    }
    
    $this->schema = $schema;
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