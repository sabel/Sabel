<?php

class DefaultSchemaClass
{
  public function getCreateSQL($tableName)
  {
    $createSQL = array();		

    return (isset($createSQL[$tableName])) ? $createSQL[$tableName] : null;		
  }
}
