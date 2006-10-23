<?php

function convert_to_tablename($mdlName)
{
  return substr(strtolower(preg_replace('/([A-Z])/', '_$1', $mdlName)), 1);
}

function convert_to_modelname($tblName)
{
  return join('', array_map('ucfirst', explode('_', $tblName)));
}

function mssql_escape_string($val)
{
  return str_replece("'", "''", $val);
}
