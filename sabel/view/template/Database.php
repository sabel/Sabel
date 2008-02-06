<?php

/**
 * Sabel_View_Template_Database
 *
 * @category   View
 * @package    org.sabel.view
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_View_Template_Database extends Sabel_View_Template
{
  protected $connectionName = "default";
  protected $tableName = "templates";
  protected $namespace = "";
  protected $contents  = "";
  
  public function setConnectionName($name)
  {
    $this->connectionName = $name;
    
    return $this;
  }
  
  public function setTableName($tblName)
  {
    $this->tableName = $tblName;
  }
  
  public function setNameSpace($namespace)
  {
    $this->namespace = $namespace;
  }
  
  public function name($name = null)
  {
    if ($name !== null) $this->contents = false;
    return parent::name($name);
  }
  
  public function getContents()
  {
    if ($this->contents === false) {
      $contents = $this->_getContents();
      if ($contents === null) $contents = "";
      return $this->contents = $contents;
    } else {
      return $this->contents;
    }
  }
  
  public function create($contents = "")
  {
    $path = $this->_getPath();
    $stmt = Sabel_DB_Package::getStatement($this->connectionName);
    
    $tblName = $stmt->quoteIdentifier($this->tableName);
    $nCol    = $stmt->quoteIdentifier("name");
    $nsCol   = $stmt->quoteIdentifier("namespace");
    $cCol    = $stmt->quoteIdentifier("contents");
    $escaped = $stmt->escape(array($path, $this->namespace, $contents));
    
    $query = "INSERT INTO {$tblName}({$nCol}, {$nsCol}, {$cCol}) "
           . "VALUES({$escaped[0]}, {$escaped[1]}, {$escaped[2]})";
           
    $stmt->setQuery($query)->execute();
    $this->contents = $contents;
  }
  
  public function delete()
  {
    $path = $this->_getPath();
    $stmt = Sabel_DB_Package::getStatement($this->connectionName);
    
    $tblName = $stmt->quoteIdentifier($this->tableName);
    $nCol    = $stmt->quoteIdentifier("name");
    $cCol    = $stmt->quoteIdentifier("contents");
    $escaped = $stmt->escape(array($path));
    
    $query = "DELETE FROM $tblName WHERE $nCol = {$escaped[0]}";
    $stmt->setQuery($query)->execute();
    $this->contents = "";
  }
  
  public function isValid()
  {
    if (($contents = $this->_getContents()) === null) {
      $this->contents = "";
      return false;
    } else {
      $this->contents = $contents;
      return true;
    }
  }
  
  private function _getContents()
  {
    $path = $this->_getPath();
    $stmt = Sabel_DB_Package::getStatement($this->connectionName);
    
    $tblName = $stmt->quoteIdentifier($this->tableName);
    $nCol    = $stmt->quoteIdentifier("name");
    $nsCol   = $stmt->quoteIdentifier("namespace");
    $cCol    = $stmt->quoteIdentifier("contents");
    $escaped = $stmt->escape(array($path, $this->namespace));
    
    $query = "SELECT $cCol FROM $tblName "
           . "WHERE $nCol = {$escaped[0]} AND $nsCol = {$escaped[1]}";
            
    $result = $stmt->setQuery($query)->execute();
    
    return ($result === null) ? null : $result[0]["contents"];
  }
  
  public function _getPath()
  {
    return $this->viewDirPath . $this->name;
  }
}
