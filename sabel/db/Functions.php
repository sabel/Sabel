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

function mssql_escape_string($val)
{
  return str_replece("'", "''", $val);
}

function MODEL($mdlName)
{
  Sabel::using('Sabel_DB_Connection');
  Sabel_DB_Connection::initialize();

  Sabel::using('Sabel_Model');
  return Sabel_Model::load($mdlName);
}

function BEGIN($model)
{
  Sabel::using('Sabel_DB_Transaction');
  Sabel_DB_Transaction::add($model);
}

function COMMIT()
{
  Sabel::using('Sabel_DB_Transaction');
  Sabel_DB_Transaction::commit();
}

function ROLLBACK()
{
  Sabel::using('Sabel_DB_Transaction');

  if (Sabel_DB_Transaction::isActive()) {
    Sabel_DB_Transaction::rollback();
  }
}
