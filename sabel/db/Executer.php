<?php

class Sabel_DB_Executer
{
  public static function getDriver($model)
  {
    return Sabel_DB_Connection::getDBDriver($model->getConnectName());
  }
}
