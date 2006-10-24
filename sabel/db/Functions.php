<?php

function convert_to_tablename($mdlName)
{
  if (preg_match('/^[a-z0-9_]+$/', $mdlName)) return $mdlName;
  return substr(strtolower(preg_replace('/([A-Z])/', '_$1', $mdlName)), 1);
}

function convert_to_modelname($tblName)
{
  return join('', array_map('ucfirst', explode('_', $tblName)));
}

function get_schema_by_tablename($tblName)
{
  $sClass = 'Schema_' . convert_to_modelname($tblName);
  return (class_exists($sClass, false)) ? new $sClass() : false;
}

function mssql_escape_string($val)
{
  return str_replece("'", "''", $val);
}
