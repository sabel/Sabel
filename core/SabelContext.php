<?php

class SabelContext
{
  private static $parameters = array();

  public static function getController()
  {
    // @TODO configuration.
    return new SabelPageWebController();
  }

  public static function setParameter($name, $value)
  {
    self::$parameters[$name] = $value;
  }

  public static function getParameter($name)
  {
    return self::$parameters[$name];
  }

  public static function getRequestModule()
  {
    return $_REQUEST['module'];
  }

  public static function getRequestAction()
  {
    return $_REQUEST['action'];
  }
}

?>
