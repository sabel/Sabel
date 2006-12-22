<?php

function get_db_tables($tblName)
{
  $tables = array();
  $tables['sversion'] = 'default';

  if (isset($tables[$tblName])) {
    return $tables[$tblName];
  } else {
    throw new Exception("Error: $tblName does not exist.");
  }
}
