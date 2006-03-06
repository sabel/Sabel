<?php

abstract class Request
{
  abstract public function get($key);
  abstract public function set($key, $value);
}

class PostRequest extends Request
{
  public function get($key)
  {
    if (isset($_POST[$key])) {
      return $_POST[$key];
    } else {
      return false;
    }
  }

  public function getRequests()
  {
    $array = array();
    foreach ($_POST as $key => $value) {
      $array[$key] = (empty($value)) ? null : $value;
    }
    return $array;
  }

  public function set($key, $value)
  {
    $_POST[$key] = $value;
  }
}

?>