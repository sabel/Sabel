<?php

/**
 * Sabel_DB_Metadata_FileWriter
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Metadata_FileWriter extends Sabel_Object
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
  
  public function write(Sabel_DB_Metadata_Table $schema)
  {
    $mdlName   = convert_to_modelname($schema->getTableName());
    $className = "Schema_" . $mdlName;
    $target    = $this->schemaDir . DS . $mdlName . PHP_SUFFIX;
    
    if (file_exists($target)) unlink($target);
    
    $lines   = array();
    $lines[] = "<?php" . PHP_EOL;
    $lines[] = "class $className";
    $lines[] = "{";
    $lines[] = "  public static function get()";
    $lines[] = "  {";
    $lines[] = '    $cols = array();' . PHP_EOL;
    
    $colLines = $this->createColumnLines($schema);
    foreach ($colLines as $line) {
      $lines[] = "    " . $line;
    }
    
    $lines[] = PHP_EOL;
    $lines[] = '    return $cols;';
    $lines[] = "  }" . PHP_EOL;
    
    $lines[] = "  public function getProperty()";
    $lines[] = "  {";
    $lines[] = '    $property = array();' . PHP_EOL;
    
    $this->writeEngine($lines, $schema);
    $this->writeUniques($lines, $schema);
    $this->writeForeignKeys($lines, $schema);
    
    $lines[] = PHP_EOL;
    $lines[] = "    return \$property;";
    $lines[] = "  }";
    $lines[] = "}";
    
    $fp = fopen($target, "w");
    fwrite($fp, implode(PHP_EOL, $lines));
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
        if ($col->min === null) $col->min = 0;
        $line[] = "'min' => {$col->min}, ";
        $line[] = "'max' => {$col->max}, ";
      }
      
      $this->setConstraints($line, $col);
      
      $line[] = "'default' => " . $this->getDefault($isNum, $col);
      $lines[$col->name] = join("", $line) . ");";
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
  
  private function writeEngine(&$lines, $schema)
  {
    $engine = $schema->getTableEngine();
    $lines[] = "    \$property['tableEngine'] = '{$engine}';";
  }
  
  private function writeUniques(&$lines, $schema)
  {
    $uniques = $schema->getUniques();
    
    if ($uniques === null) {
      $lines[] = "    \$property['uniques'] = null;";
    } else {
      foreach ($uniques as $unique) {
        $us = array();
        foreach ($unique as $u) $us[] = "'" . $u . "'";
        $us = implode(", ", $us);
        $lines[] = "    \$property['uniques'][] = array({$us});";
      }
    }
  }

  private function writeForeignKeys(&$lines, $schema)
  {
    $fkey = $schema->getForeignKey();
    
    if ($fkey === null) {
      $lines[] = "    \$property['fkeys'] = null;";
    } else {
      $space = "                                         ";
      foreach ($fkey->toArray() as $column => $params) {
        $lines[] = "    \$property['fkeys']['{$column}'] = "
                 . "array('referenced_table'  => '{$params->table}',";
                 
        $lines[] = $space . "'referenced_column' => '{$params->column}',";
        $lines[] = $space . "'on_delete'         => '{$params->onDelete}',";
        $lines[] = $space . "'on_update'         => '{$params->onUpdate}');";
      }
    }
  }
}
