<?php

class Sanitize
{
  /**
   * remove non alphabet and non numeric characers.
   *
   * @param mixed  $targe string or array
   * @param string $expected
   * @return mixed cleaned string or array
   */
  public static function removeNonAlphaNumeric($target, $expected = null)
  {
    $expected = ($expected) ? $expected : "";

    if(is_array($target)) {
      foreach ($target as $key => $clean) {
        $cleaned[$key] = preg_replace( "/[^${$expected}a-zA-Z0-9]/", '', $clean);
      }
    } else {
      $cleaned = preg_replace( "/[^${$expected}a-zA-Z0-9]/", "", $target);
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

  public static function normalize($target)
  {
    if (get_magic_quotes_gpc()) {
      if (is_array($target)) {
        foreach ($target as $k => $v) {
          $target[$k] = stripslashes($v);
        }
      } else {
        $target = stripslashes($target);
      }
    }

    return $target;
  }
}

?>
