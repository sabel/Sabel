<?php

/**
 * Manipulator
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Manipulator extends Sabel_DB_Manipulator
{
  const CREATED_COLUMN = "created";
  const UPDATED_COLUMN = "updated";
  const DELETED_COLUMN = "deleted";
  
  public function before($method)
  {
    switch ($method) {
      case "save":
        return $this->beforeSave();
        
      case "insert":
        return $this->beforeInsert();
        
      case "update":
        return $this->beforeUpdate();
    }
  }
  
  public function after($method, $result)
  {
    if (ENVIRONMENT === DEVELOPMENT) {
      $this->log();
    }
  }
  
  private function beforeSave()
  {
    $model    = $this->model;
    $columns  = $model->getColumnNames();
    $datetime = now();
    
    if (in_array(self::UPDATED_COLUMN, $columns)) {
      $model->{self::UPDATED_COLUMN} = $datetime;
    }
    
    if (!$model->isSelected()) {
      if (in_array(self::CREATED_COLUMN, $columns)) {
        $model->{self::CREATED_COLUMN} = $datetime;
      }
    }
    
    $args = $this->arguments;
    
    if (isset($args[0]) && is_array($args[0])) {
      $validator = new Sabel_DB_Validator($model);
      $errors = $validator->validate($args[0]);
      if ($errors) return $errors;
    }
  }
  
  private function beforeInsert()
  {
    if (!isset($this->arguments[0])) return;
    $columns  = $this->model->getColumnNames();
    $datetime = now();
    
    if (in_array(self::UPDATED_COLUMN, $columns)) {
      $this->arguments[0][self::UPDATED_COLUMN] = $datetime;
    }
    
    if (in_array(self::CREATED_COLUMN, $columns)) {
      $this->arguments[0][self::CREATED_COLUMN] = $datetime;
    }
  }
  
  private function beforeUpdate()
  {
    if (!isset($this->arguments[0])) return;
    $columns = $this->model->getColumnNames();
    
    if (in_array(self::UPDATED_COLUMN, $columns)) {
      $this->arguments[0][self::UPDATED_COLUMN] = now();
    }
  }
  
  private function log()
  {
    static $selectLog = null;
    static $insertLog = null;
    static $updateLog = null;
    static $deleteLog = null;
    static $queryLog  = null;
    
    $stmt = $this->stmt;
    if (is_object($stmt)) {
      if ($stmt->isSelect()) {
        $name = "select";
      } elseif ($stmt->isInsert()) {
        $name = "insert";
      } elseif ($stmt->isUpdate()) {
        $name = "update";
      } elseif ($stmt->isDelete()) {
        $name = "delete";
      } else {
        $name = "query";
      }
      
      $logger = $name . "Log";
      if ($$logger === null) {
        $$logger = new Sabel_Logger_File($name . ".log");
      }
      
      $sql = $stmt->getSql();
      if ($bindParams = $stmt->getBindParams()) {
        $bindParams = $stmt->getDriver()->escape($bindParams);
        $sql = str_replace(array_keys($bindParams), $bindParams, $sql);
      }
      
      $$logger->log($sql);
    }
  }
}
