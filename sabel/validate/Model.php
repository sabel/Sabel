<?php

/**
 * Sabel Validator for model
 *
 * @category   Validate
 * @package    org.sabel.validate
 * @author     Mori Reo <mori.reo@gmail.com>
 * @author     Ebin Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Validate_Model extends Sabel_Validate_Validator
{
  protected $errors  = null;
  protected $mdlName = '';
  protected $conName = '';
  protected $scmName = '';

  public function __construct($modelName)
  {
    $model = new $modelName();
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
        } elseif (empty($data[$name])) {
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
    
    switch ($type) {
      case Sabel_DB_Schema_Const::STRING:
        $error = false;
        if (function_exists('mb_strlen')) {
          $error = (mb_strlen($value, 'UTF-8') > $max);
        } else {
          $error = (strlen($value) > $max);
        }
        
        if ($error) return array("{$name} must lower then " . $max, Sabel_Validate_Error::LOWER_THEN);
        break;
      case Sabel_DB_Schema_Const::INT:
        if (!is_numeric($value)) {
          return array("{$name} must be numeric", Sabel_Validate_Error::GRATHER_THEN);
        } elseif ($value > $max) {
          return array("{$name} must lower then " . $max, Sabel_Validate_Error::LOWER_THEN);
        } elseif ($value < $min) {
          return array("{$name} must grather then " . $min, Sabel_Validate_Error::GRATHER_THEN);
        }
        break;
    }
  }
}
