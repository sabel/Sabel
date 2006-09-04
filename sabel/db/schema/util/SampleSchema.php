<?php

class Sample
{
  public function getColumnInfo()
  {
    $columns = array();

    $columns['id']      = 'INT,222,-222,false,false,false,null';
    $columns['name']    = 'STRING,128,false,true,false,null';
    $columns['status']  = 'BOOL,false,true,false,null';
    $columns['comment'] = 'STRING,64,false,false,false,varchar default';
    $columns['pare_id'] = 'INT,444,-444,true,false,true,null';
    $columns['birth']   = 'DATE,false,true,false,3000-01-01';
    $columns['time']    = 'TIMESTAMP,false,false,false,null';
    $columns['com']     = 'TEXT,false,false,false,null';

    return $columns;
  }
}
