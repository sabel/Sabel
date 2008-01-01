<?php

/**
 * Sabel_DB_Migration_Create
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Migration_Create
{
  private
    $mcolumns = array(),
    $columns  = array(),
    $pkeys    = array(),
    $fkeys    = array(),
    $uniques  = array(),
    $indexes  = array(),
    $options  = array();
    
  public function column($name)
  {
    $mcolumn = new Sabel_DB_Migration_Column($name);
    return $this->mcolumns[$name] = $mcolumn;
  }
  
  public function build()
  {
    $columns = array();
    
    foreach ($this->mcolumns as $column) {
      $columns[] = $column->arrange()->getColumn();
    }
    
    $pkeys =& $this->pkeys;
    foreach ($columns as $column) {
      if ($column->primary) $pkeys[] = $column->name;
    }
    
    $this->columns = $columns;
    
    return $this;
  }
  
  public function getColumns()
  {
    return $this->columns;
  }
  
  public function getPrimaryKeys()
  {
    return $this->pkeys;
  }
  
  public function getForeignKeys()
  {
    return $this->fkeys;
  }
  
  public function getUniques()
  {
    return $this->uniques;
  }
  
  public function getIndexes()
  {
    return $this->indexes;
  }
  
  public function getOptions()
  {
    return $this->options;
  }
  
  public function primary($columnNames)
  {
    if (is_string($columnNames)) {
      $this->pkeys = array($columnNames);
    } elseif (is_array($columnNames)) {
      $this->pkeys = $columnNames;
    } else {
      Sabel_Command::error("primary() argument must be a string or an array.");
      exit;
    }
  }
  
  public function unique($columnNames)
  {
    if (is_string($columnNames)) {
      $this->uniques[] = (array)$columnNames;
    } elseif (is_array($columnNames)) {
      $this->uniques[] = $columnNames;
    } else {
      Sabel_Command::error("unique() argument should be a string or an array.");
      exit;
    }
  }
  
  public function fkey($colName)
  {
    $fKey = new Sabel_DB_Migration_ForeignKey($colName);
    return $this->fkeys[$colName] = $fKey;
  }
  
  public function index($colName)
  {
    $this->indexes[] = $colName;
  }
  
  public function options($key, $val)
  {
    $this->options[$key] = $val;
  }
}
