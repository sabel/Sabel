<?php

interface Sabel_Container_Lifecycle_Backend
{
  public function store($className, Array $properties);
  public function fetch($className, $instance, $reflection, Array $properties);
  public function isStored($className);
}
