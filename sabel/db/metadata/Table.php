<?php

/**
 * Sabel_DB_Metadata_Table
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Metadata_Table extends Sabel_Object
{
  protected $tableName       = "";
  protected $columns         = array();
  protected $primaryKey      = null;
  protected $foreignKeys     = null;
  protected $uniques         = null;
  protected $incrementColumn = null;
  protected $tableEngine     = null;
  
  public function __construct($name, $columns)
  {
    $this->tableName = $name;
    $this->columns   = $columns;
    
    $this->setPrimaryKey();
    $this->setSequenceColumn();
  }
  
  public function setColumn(Sabel_DB_Metadata_Column $column)
  {
    $this->columns[$column->name] = $column;
  }
  
  public function __get($key)
  {
    return (isset($this->columns[$key])) ? $this->columns[$key] : null;
  }
  
  public function getTableName()
  {
    return $this->tableName;
  }
  
  public function getColumns()
  {
    return $this->columns;
  }
  
  public function getColumnByName($name)
  {
    return $this->__get($name);
  }
  
  public function getColumnNames()
  {
    return array_keys($this->columns);
  }
  
  public function hasColumn($name)
  {
    return isset($this->columns[$name]);
  }
  
  public function getPrimaryKey()
  {
    return $this->primaryKey;
  }
  
  public function getSequenceColumn()
  {
    return $this->incrementColumn;
  }
  
  public function setForeignKeys($fkeys)
  {
    if ($fkeys === null) return;
    $this->foreignKey = new Sabel_DB_Metadata_ForeignKey($fkeys);
  }
  
  public function getForeignKey()
  {
    return $this->foreignKey;
  }
  
  public function isForeignKey($colName)
  {
    return isset($this->foreignKeys[$colName]);
  }
  
  public function setUniques($uniques)
  {
    $this->uniques = $uniques;
  }
  
  public function getUniques()
  {
    return $this->uniques;
  }
  
  public function isUnique($colName)
  {
    $uniques = $this->uniques;
    if ($uniques === null) return false;
    
    foreach ($uniques as $unique) {
      if (in_array($colName, $unique)) return true;
    }
    
    return false;
  }
  
  public function setTableEngine($engine)
  {
    $this->tableEngine = $engine;
  }
  
  public function getTableEngine()
  {
    return $this->tableEngine;
  }
  
  private function setPrimaryKey()
  {
    $pKey = array();
    foreach ($this->columns as $column) {
      if ($column->primary) $pKey[] = $column->name;
    }
    
    if (empty($pKey)) {
      $this->primaryKey = null;
    } elseif (count($pKey) === 1) {
      $this->primaryKey = $pKey[0];
    } else {
      $this->primaryKey = $pKey;
    }
  }
  
  private function setSequenceColumn()
  {
    $incrementColumn = null;
    
    foreach ($this->columns as $column) {
      if ($column->increment) {
        $incrementColumn = $column->name;
        break;
      }
    }
    
    $this->incrementColumn = $incrementColumn;
  }
}
