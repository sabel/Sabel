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

  public static $INTS  = array('integer',
                               'int',
                               'bigint',
                               'smallint',
                               'tinyint',
                               'mediumint');

  public static $STRS  = array('varchar',
                               'char',
                               'character varying',
                               'character');

  public static $TEXTS = array('text',
                               'mediumtext',
                               'tinytext');

  public static $BLOBS = array('blob',
                               'bytea',
                               'longblog',
                               'mediumblob');

  public static $TIMES = array('timestamp',
                               'timestamp without time zone',
                               'timestamp with time zone',
                               'datetime');
}
