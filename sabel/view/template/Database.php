<?php

/**
 * Sabel_View_Template_Database
 *
 * @category   View
 * @package    org.sabel.view
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_View_Template_Database extends Sabel_View_Template
{
  protected $connectionName = "default";
  protected $tableName = "templates";
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
    $path = $this->getPath();
    $stmt = Sabel_DB_Driver::createStatement($this->connectionName);
    
    $tblName = $stmt->quoteIdentifier($this->tableName);
    $escaped = $stmt->escape(array($path, $contents));
    
    $query = "INSERT INTO $tblName VALUES({$escaped[0]}, {$escaped[1]})";
    $stmt->setQuery($query)->execute();
    $this->contents = $contents;
  }
  
  public function delete()
  {
    $path = $this->getPath();
    $stmt = Sabel_DB_Driver::createStatement($this->connectionName);
    
    $tblName = $stmt->quoteIdentifier($this->tableName);
    $nameCol = $stmt->quoteIdentifier("name");
    $escaped = $stmt->escape(array($path));
    
    $query = "DELETE FROM $tblName WHERE $nameCol = {$escaped[0]}";
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
    $path = $this->getPath();
    $stmt = Sabel_DB_Driver::createStatement($this->connectionName);
    
    $tblName     = $stmt->quoteIdentifier($this->tableName);
    $nameCol     = $stmt->quoteIdentifier("name");
    $contentsCol = $stmt->quoteIdentifier("contents");
    $escaped     = $stmt->escape(array($path));
    
    $query  = "SELECT $contentsCol FROM $tblName WHERE $nameCol = {$escaped[0]}";
    $result = $stmt->setQuery($query)->execute();
    
    return ($result === null) ? null : $result[0]["contents"];
  }
}
