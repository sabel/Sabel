<?php

/**
 * Sabel_DB_Schema_Type
 * RDBMS type mappings for Sabel internal.
 *
 * @package org.sabel.db.schema
 * @author Ebine Yutaka <ebine.yutaka@gmail.com>
 */
class Sabel_DB_Schema_Type
{
  const INT       = 'INT';
  const STRING    = 'STRING';
  const TEXT      = 'TEXT';
  const BOOL      = 'BOOL';
  const BLOB      = 'BLOB';
  const DATE      = 'DATE';
  const TIME      = 'TIME';
  const TIMESTAMP = 'TIMESTAMP';

  public static function setRange($columnObj, $intType)
  {
    switch($intType) {
      case 'tinyint':
        $columnObj->max =  127;
        $columnObj->min = -128;
        break;
      case 'int2':
      case 'smallint':
        $columnObj->max =  32767;
        $columnObj->min = -32768;
        break;
      case 'int3':
      case 'mediumint':
        $columnObj->max =  8388607;
        $columnObj->min = -8388608;
        break;
      case 'int':
      case 'int4':
      case 'integer':
      case 'serial':
        $columnObj->max =  2147483647;
        $columnObj->min = -2147483648;
        break;
      case 'int8':
      case 'bigint':
      case 'bigserial':
        $columnObj->max =  9223372036854775807;
        $columnObj->min = -9223372036854775808;
        break;
    }
  }
}
