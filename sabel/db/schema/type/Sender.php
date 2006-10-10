<?php

interface Sabel_DB_Schema_Type_Sender
{
  public function add($chain);
  public function send($columnObj, $type);
}