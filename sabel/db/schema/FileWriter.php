<?php

/**
 * Sabel_DB_Schema_FileWriter
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Schema_FileWriter extends Sabel_Object
{
  private $schemaDir = "";
  
  public function __construct($schemaDir)
  {
    if (is_dir($schemaDir)) {
      $this->schemaDir = $schemaDir;
    } else {
      throw new Sabel_DB_Exception("no such directory: '{$schemaDir}'");
    }
  }
  
  public function write(Sabel_DB_Schema_Table $schema)
  {
    $mdlName   = convert_to_modelname($schema->getTableName());
    $className = "Schema_" . $mdlName;
    $target    = $this->schemaDir . DS . $mdlName . PHP_SUFFIX;
    
    if (file_exists($target)) unlink($target);
    
    $fp = fopen($target, "w");
    
    fwrite($fp, "<?php\n\n");
    fwrite($fp, "class {$className}\n{\n");
    fwrite($fp, "  public static function get()\n  {\n");
    fwrite($fp, '    $cols = array();');
    fwrite($fp, "\n\n");
    
    $colLines = $this->createColumnLines($schema);
    foreach ($colLines as $line) fwrite($fp, "    " . $line);
    
    fwrite($fp, "\n    return " . '$cols;' . "\n  }\n");
    
    $property   = array();
    $property[] = '$property = array();' . "\n\n";
    
    $this->writeEngine($property, $schema);
    $this->writeUniques($property, $schema);
    $this->writeForeignKeys($property, $schema);
    
    fwrite($fp, "\n  public function getProperty()\n  {\n");
    fwrite($fp, "    " . join("", $property));
    fwrite($fp, "    " . 'return $property;' . "\n  }\n}\n");
    fclose($fp);
  }
  
  private function createColumnLines($schema)
  {
    $lines   = array();
    $columns = $schema->getColumns();
    
    foreach ($columns as $col) {
      $line  = array();
      $isNum = false;
      
      $line[] = '$cols[' . "'{$col->name}'] = array(";
      
      $type   = str_replace("_", "", $col->type);
      $line[] = "'type' => Sabel_DB_Type::{$type}, ";
      
      if ($col->isInt() || $col->isFloat() || $col->isDouble()) {
        $line[] = "'max' => {$col->max}, ";
        $line[] = "'min' => {$col->min}, ";
        $isNum = true;
      } elseif ($col->isString()) {
        $line[] = "'max' => {$col->max}, ";
      }
      
      $this->setConstraints($line, $col);
      
      $line[] = "'default' => " . $this->getDefault($isNum, $col);
      $lines[$col->name] = join("", $line) . ");\n";
    }
    
    return $lines;
  }
  
  private function setConstraints(&$line, $column)
  {
    $increment = ($column->increment) ? "true" : "false";
    $nullable  = ($column->nullable)  ? "true" : "false";
    $primary   = ($column->primary)   ? "true" : "false";
    
    $line[] = "'increment' => {$increment}, ";
    $line[] = "'nullable' => {$nullable}, ";
    $line[] = "'primary' => {$primary}, ";
  }
  
  private function getDefault($isNum, $column)
  {
    $default = $column->default;
    
    if ($default === null) {
      $str = "null";
    } elseif ($isNum) {
      $str = $default;
    } elseif ($column->isBool()) {
      $str = ($default) ? "true" : "false";
    } else {
      $str = "'" . $default . "'";
    }
    
    return $str;
  }
  
  private function writeEngine(&$property, $schema)
  {
    $engine = $schema->getTableEngine();
    $property[] = '    $property' . "['tableEngine'] = '{$engine}';\n";
  }
  
  private function writeUniques(&$property, $schema)
  {
    $uniques = $schema->getUniques();
    
    if ($uniques === null) {
      $property[] = '    $property' . "['uniques'] = null;\n";
    } else {
      foreach ($uniques as $unique) {
        $us = array();
        foreach ($unique as $u) $us[] = "'" . $u . "'";
        $us = implode(", ", $us);
        $property[] = '    $property' . "['uniques'][] = array({$us});\n";
      }
    }
  }

  private function writeForeignKeys(&$property, $schema)
  {
    $fkeys = $schema->getForeignKeys();
    
    if ($fkeys === null) {
      $property[] = '    $property' . "['fkeys'] = null;\n";
    } else {
      $space = "                                         ";
      foreach ($fkeys as $column => $params) {
        $property[] = '    $property' . "['fkeys']['{$column}'] = ";
        $property[] = "array('referenced_table'  => '{$params['referenced_table']}',\n";
        $property[] = $space . "'referenced_column' => '{$params['referenced_column']}',\n";
        $property[] = $space . "'on_delete'         => '{$params['on_delete']}',\n";
        $property[] = $space . "'on_update'         => '{$params['on_update']}');\n";
      }
    }
  }
}
