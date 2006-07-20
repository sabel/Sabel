<?php

interface Sabel_Cache_Cache
{
  public function get($key);
  public function add($key, $value);
  public function delete($key);
}