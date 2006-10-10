<?php

abstract class Sabel_DB_Tree extends Sabel_DB_Mapper
{
  protected $structure = 'tree';

  public function getRoot()
  {
    return $this->select("{$this->table}_id", 'null');
  }
}
