<?php

/**
 * Sabel_DB_Migration_Column
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Migration_Column
{
  /**
   * @var Sabel_DB_Metadata_Column
   */
  private $column = null;
  
  /**
   * @var boolean
   */
  private $isChange = false;
  
  public function __construct($name, $isChange = false)
  {
    $this->column = new Sabel_DB_Metadata_Column();
    $this->column->name = $name;
    $this->isChange = $isChange;
  }
  
  public function getColumn()
  {
    return $this->column;
  }
  
  public function type($type)
  {
    $const = @constant($type);
    
    if ($const === null) {
      Sabel_Command::error("datatype '{$type}' is not supported.");
      exit;
    } else {
      $this->column->type = $const;
    }
    
    return $this;
  }
  
  public function primary($bool)
  {
    $this->setBoolean($bool, "primary");
    return $this;
  }
  
  public function increment($bool)
  {
    $this->setBoolean($bool, "increment");
    return $this;
  }
  
  public function nullable($bool)
  {
    $this->setBoolean($bool, "nullable");
    return $this;
  }
  
  public function value($value)
  {
    if ($this->column->isBool() && !is_bool($value)) {
      Sabel_Command::error("default value for BOOL column should be a boolean.");
      exit;
    } else {
      $this->column->default = $value;
      return $this;
    }
  }
  
  public function length($length)
  {
    if ($this->column->isString() || $this->isChange && $this->column->type === null) {
      $this->column->max = $length;
      return $this;
    } else {
      Sabel_Command::error("length() for _STRING column.");
      exit;
    }
  }
  
  private function setBoolean($bool, $key)
  {
    if (is_bool($bool)) {
      $this->column->$key = $bool;
    } else {
      Sabel_Command::error("argument for {$key}() should be a boolean.");
      exit;
    }
  }
  
  public function arrange()
  {
    $column = $this->column;
    
    if ($column->primary === true) {
      $column->nullable = false;
    } elseif ($column->nullable === null) {
      $column->nullable = true;
    }
    
    if ($column->primary === null) {
      $column->primary = false;
    }
    
    if ($column->increment === null) {
      $column->increment = false;
    }
    
    if ($column->type === Sabel_DB_Type::STRING &&
        $column->max === null) $column->max = 255;
        
    if ($column->type === Sabel_DB_Type::INT) {
      if ($column->max === null) $column->max = PHP_INT_MAX;
      if ($column->min === null) $column->min = -PHP_INT_MAX - 1;
    }
    
    if ($column->type === Sabel_DB_Type::SMALLINT) {
      if ($column->max === null) $column->max = 32767;
      if ($column->min === null) $column->min = -32768;
    }
    
    return $this;
  }
}
