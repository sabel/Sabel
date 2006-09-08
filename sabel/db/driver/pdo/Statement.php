<?php

class Sabel_DB_Driver_Pdo_Statement
{
  private static $stmt  = null;
  private static $sql   = '';
  private static $keys  = array();
  private static $const = array();

  public static function exists($sql, $cond, $const = null)
  {
    $result = true;
    $keys   = array();

    if ($cond) $keys = array_keys($cond);

    if (self::$sql !== $sql || self::$keys !== $keys || self::$const !== $const) {
      self::$sql   = $sql;
      self::$keys  = $keys;
      self::$const = $const;

      $result = false;
    }
    return $result;
  }

  public static function add($stmt)
  {
    self::$stmt = $stmt;
  }

  public static function get()
  {
    return self::$stmt;
  }
}
