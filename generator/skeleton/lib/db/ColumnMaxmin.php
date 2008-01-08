<?php

class Db_ColumnMaxmin extends Sabel_Object
{
  private static $instance = null;
  
  private function __construct() {}
  
  public static function create()
  {
    if (self::$instance === null) {
      self::$instance = new self();
    }
    
    return self::$instance;
  }
  
  /*
  public function {TABLE_NAME}()
  {
    $cols = array();
    $cols["{COLUMN_NAME}"] = array("min" => {MIN}, "max" => {MAX});
    
    return $cols;
  }
  */
}
