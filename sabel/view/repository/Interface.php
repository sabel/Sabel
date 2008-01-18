<?php

/**
 * Sabel_View_Repository_Interface
 *
 * @interface
 * @category   View
 * @package    org.sabel.view
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
interface Sabel_View_Repository_Interface
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
  public function getResourceList($locationName);
  public function isResourceValid($locationName, $name);
}
