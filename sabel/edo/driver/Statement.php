<?php

class Sabel_Edo_Driver_Statement
{
  private static $stmt;
  private static $sql;
  private static $keys = array();
  private static $constraints = array();

  public static function statement_exists($sql, $conditions, $constraints = null)
  {
    $result = true;
    $keys = array_keys($conditions);
    
    if (self::$sql         != $sql  || 
        self::$keys        != $keys || 
        self::$constraints != $constraints) { 

      self::$sql         = $sql;
      self::$keys        = $keys;
      self::$constraints = $constraints;
      $result = false;
    }        

    return $result;
  }

  public static function addStatement($stmt)
  {
    self::$stmt = $stmt;
  }

  public static function getStatement()
  {
    return self::$stmt;
  }
}

?>
