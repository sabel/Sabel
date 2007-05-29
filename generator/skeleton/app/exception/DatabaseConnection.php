<?php

class Exception_DatabaseConnection
{
  public static function error($connectionName)
  {
    throw new Sabel_Exception_Runtime("check your database connectivity");
  }
}
