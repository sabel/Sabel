<?php

interface Sabel_DB_Command
{
  const SELECT = 0x01;
  const INSERT = 0x02;
  const UPDATE = 0x04;
  const DELETE = 0x08;
  const QUERY  = 0x10;

  const BEGIN    = 0x20;
  const COMMIT   = 0x40;
  const ROLLBACK = 0x80;

  const ARRAY_INSERT = 0x100;
}
