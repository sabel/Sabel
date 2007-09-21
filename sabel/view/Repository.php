<?php

interface Sabel_View_Repository
{
  /**
   * find resource from locations
   *
   * @param string $action defautl null
   */
  public function find($action = null);
  public function getResourceFromLocation($locationName, $name);
  public function createResource($locationName, $body, $action = null);
  public function editResource($locationName, $body, $action = null);
  public function deleteResource($locationName, $action = null);
}
