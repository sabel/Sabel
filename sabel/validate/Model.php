<?php

/**
 * Sabel Validator for model
 *
 * @category   Validate
 * @package    org.sabel.validate
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Validate_Model extends Sabel_Validate_Validator
{
  protected $errors = null;
  protected $schema = null;
  
  public function validate($data)
  {
    $schema = $this->schema;
    if (!is_object($schema))
      throw new Sabel_Exception_Runtime("Schema must be Object");
      
    $this->errors = new Sabel_Validate_Errors();
    
    foreach ($schema->get() as $name => $column) {
      if ($column['notNull'] === true) {
        if (!isset($data[$name])) {
          $this->errors->add($name, "$name must be not null", Sabel_Validate_Error::NOT_NULL); 
        }
      }
      
      if(isset($data[$name]) && (list($msg, $type) = $this->isError($column, $data[$name], $name))) {
        $this->errors->add($name, $msg, $type);
      }
      
      /* @todo implement custom validator
      if ($this->hasCustom($column->name)) {
        $this->custom($column->name, $data[$column->name]);
      }
      */
    }
    
    return $this->errors;
  }
  
  public function isError($column, $value, $name = null)
  {
    extract($column);
    
    $strlen = (function_exists('mb_strlen')) ? 'mb_strlen' : 'strlen';
    
    switch ($type) {
      case 'STRING':
        switch ($value) {
          case ($strlen($value) > $max):
            return array("{$name} must lower then " . $max, Sabel_Validate_Error::LOWER_THEN);
            break;
        }
        break;
      case 'INT':
        switch ($value) {
          case ($value > $max):
            return array("{$name} must lower then " . $max, Sabel_Validate_Error::LOWER_THEN);
            break;
          case ($value < $min):
            return array("{$name} must grather then " . $min, Sabel_Validate_Error::GRATHER_THEN);
            break;
        }
        break;
    }
  }
}