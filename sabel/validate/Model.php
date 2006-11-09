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
  protected $errors  = null;
  protected $mdlName = '';
  protected $conName = '';
  protected $scmName = '';

  public function __construct($model)
  {
    $conName = $model->connectName;

    $this->mdlName = get_class($model);
    $this->conName = $conName;
    $this->scmName = Sabel_DB_Connection::getSchema($conName);
  }
  
  public function validate($data)
  {
    $sClass = 'Schema_' . ucfirst($this->mdlName);

    if (class_exists($sClass, false)) {
      $schema  = new $sClass();
      $columns = $schema->get();
    } else {
      $tblName = convert_to_tablename($this->mdlName);
      $sAccess = new Sabel_DB_Schema_Accessor($this->conName, $this->scmName);
      $columns = $sAccess->getTable($tblName)->getColumns();
    }
    
    $this->errors = new Sabel_Validate_Errors();

    foreach ($columns as $name => $column) {
      if ($column['nullable'] === false && $column['increment'] === true) continue;
        
      if ($column['nullable'] === false && $column['increment'] === false)
      {
        if (!isset($data[$name])) {
          $this->errors->add($name, "$name can't be blank", null, Sabel_Validate_Error::NOT_NULL);
          continue;
        } else if (empty($data[$name])) {
          $this->errors->add($name, "$name can't be blank", null, Sabel_Validate_Error::NOT_NULL);
          continue;
        }
      }
      
      if (isset($data[$name])) {
        if (list($msg, $type) = $this->isError($column, $data[$name], $name)) {
          $this->errors->add($name, $msg, $data[$name], $type);
        }
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
      case Sabel_DB_Schema_Const::STRING:
        switch ($value) {
          case ($strlen($value) > $max):
            return array("{$name} must lower then " . $max, Sabel_Validate_Error::LOWER_THEN);
            break;
        }
        break;
      case Sabel_DB_Schema_Const::INT:
        switch ($value) {
          case ($value > $max):
            return array("{$name} must lower then " . $max, Sabel_Validate_Error::LOWER_THEN);
            break;
          case ($value < $min):
            return array("{$name} must grather then " . $min, Sabel_Validate_Error::GRATHER_THEN);
            break;
          case (!is_numeric($value)):
            return array("{$name} must be numeric", Sabel_Validate_Error::GRATHER_THEN);
            break;
        }
        break;
    }
  }
}
