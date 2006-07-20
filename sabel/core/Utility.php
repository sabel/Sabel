<?php

class Sanitize
{
  /**
   * remove non alphabet and non numeric characers.
   *
   * @param mixed  $target string or array
   * @param string $expected
   * @return mixed cleaned string or array
   */
  public static function removeNonAlphaNumeric(&$target, $expected = '')
  {
    $cleaned = null;

    if(is_array($target)) {
      foreach ($target as $key => $value) {
        $cleaned[$key] = preg_replace( "/[^${$expected}a-zA-Z0-9]/", '', $value);
      }
    } else {
      $cleaned = preg_replace( "/[^${$expected}a-zA-Z0-9]/", '', $target);
    }

    return $cleaned;
  }

  /**
   * target SQL string to make SQL safety
   *
   */
  public static function sqlSafe($target)
  {
    return addslashes($target);
  }

  public static function normalize(&$target)
  {
    $cleaned = null;

    if (get_magic_quotes_gpc()) {
      if (is_array($target)) {
        foreach ($target as $key => $value) {
          $cleaned[$k] = stripslashes($value);
        }
      } else {
        $cleaned = stripslashes($target);
      }
    }

    return $cleaned;
  }
}