<?php

/**
 * Sabel_DB_Schema_Sqlite
 *
 * @category   DB
 * @package    org.sabel.db
 * @subpackage schema
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Schema_SQLite extends Sabel_DB_Schema_Common
{
  protected
    $tableList    = "SELECT name FROM sqlite_master WHERE type = 'table'",
    $tableColumns = "SELECT * FROM sqlite_master WHERE name = '%s'";

  private
    $floatTypes  = array('float', 'float4', 'real'),
    $doubleTypes = array('float8', 'double');

  private $colLine = '';

  public function __construct($connectName, $schema = null)
  {
    $this->driver = Sabel_DB_Connection::getDriver($connectName);
  }

  public function getTableNames()
  {
    $tables = array();

    $this->driver->execute($this->tableList);
    foreach ($this->driver->getResultSet() as $row) $tables[] = $row['name'];
    return $tables;
  }

  public function getTables()
  {
    $tables = array();
    foreach ($this->getTableNames() as $tblName) {
      $tables[$tblName] = $this->getTable($tblName);
    }
    return $tables;
  }

  protected function createColumns($table)
  {
    $this->driver->execute(sprintf($this->tableColumns, $table));
    $assocRow = $this->driver->getResultSet()->fetch();

    return $this->create($assocRow['sql']);
  }

  private function create($createSQL)
  {
    $constLine = '';

    $lines   = $this->splitCreateSQL($createSQL);
    $columns = array();
    foreach ($lines as $key => $line) {
      $co    = new Sabel_DB_Schema_Column();
      $split = explode(' ', $line);
      $name  = $split[0];
      $attr  = trim(substr($line, strlen($name)));

      $co->name = $name;

      if (strpos($line, 'primary key') !== false && strpbrk($line, '(') !== false) {
        $constLine = $line . ',' . $lines[$key + 1];
        break;
      }

      $this->setIncrement($co, $attr);

      if ($this->setDataType($co, $attr)) {
        $this->setNotNull($co);
        $this->setPrimary($co);
        $this->setDefault($co);
      }
      $columns[$name] = $co;
    }

    if ($constLine !== '') $columns = $this->setConstraint($columns, $constLine);
    return $columns;
  }

  private function splitCreateSQL($sql)
  {
    $sql   = substr(strpbrk($sql, '('), 0);
    $lines = explode(',', substr($sql, 1, -1));
    return array_map('strtolower', array_map('trim', $lines));
  }

  private function setIncrement($co, $attributes)
  {
    $pri  = (strpos($attributes, 'integer primary key') !== false);
    $pri2 = (strpos($attributes, 'integer not null primary key') !== false);

    $co->increment = ($pri || $pri2);
  }

  private function setDataType($co, $attributes)
  {
    $tmp     = substr($attributes, 0, strpos($attributes, ' '));
    $type    = ($tmp === '') ? $attributes : $tmp;
    $colLine = substr($attributes, strlen($type));

    if ($this->isBoolean($type)) {
      $co->type = Sabel_DB_Schema_Const::BOOL;
    } elseif (!$this->isString($co, $type)) {
      if ($this->isFloat($type)) $type = $this->getFloatType($type);
      Sabel_DB_Schema_Type_Setter::send($co, $type);
    }

    if ($colLine === '') {
      $co->nullable = true;
      $co->primary  = false;
      $co->default  = null;
      return false;
    } else {
      $this->colLine = $colLine;
      return true;
    }
  }

  private function isBoolean($type)
  {
    return ($type === 'boolean' || $type === 'bool');
  }

  private function isFloat($type)
  {
    return (in_array($type, $this->floatTypes) || in_array($type, $this->doubleTypes));
  }

  private function getFloatType($type)
  {
    return (in_array($type, $this->floatTypes)) ? 'float' : 'double';
  }

  private function isString($co, $type)
  {
    $types = array('varchar', 'char', 'character');

    foreach ($types as $sType) {
      if (strpos($type, $sType) !== false) {
        $length   = strpbrk($type, '(');
        $co->type = Sabel_DB_Schema_Const::STRING;
        $co->max  = ($length === false) ? 255 : (int)substr($length, 1, -1);
        return true;
      }
    }
    return false;
  }

  private function setNotNull($co)
  {
    $co->nullable  = (strpos($this->colLine, 'not null') === false);
    $this->colLine = str_replace('not null', '', $this->colLine);
  }

  private function setPrimary($co)
  {
    if ($this->colLine === '') {
      $co->primary = false;
    } else {
      $co->primary   = (strpos($this->colLine, 'primary key') !== false);
      $this->colLine = str_replace('primary key', '', $this->colLine);
    }
  }

  private function setDefault($co)
  {
    if (strpos($this->colLine, 'default') !== false) {
      $default = trim(str_replace('default ', '', $this->colLine));
      if ($co->type === Sabel_DB_Schema_Const::BOOL) {
        $co->default = (strcasecmp($default, 'true') === 0 || $default === '1');
      } else {
        $co->default = (is_numeric($default)) ? (int)$default : substr($default, 1, -1);
      }
    } else {
      $co->default = null;
    }
  }

  private function setConstraint($columns, $line)
  {
    $line = strpbrk($line, '(');
    if (strpbrk($line, ',') !== false) {
      $parts = explode(',', $line);
      foreach ($parts as $key => $part) $parts[$key] = str_replace(array('(', ')'), '', $part);
      foreach ($parts as $key) $columns[$key]->primary = true;
    } else {
      $priCol = substr($line, 1, -1);
      $columns[$priCol]->primary = true;
    }
    return $columns;
  }
}
