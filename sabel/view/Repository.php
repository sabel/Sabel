<?php

interface Sabel_View_Repository
{
  public function find($action = null);
  public function getByLocation($locationName, $name);
  public function createResource($locationName, $body, $action = null);
  public function deleteResource($locationName, $action = null);
}
