<?php

/**
 * Sabel_Controller_Executer
 *
 * @category   Controller
 * @package    org.sabel.controller
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
interface Sabel_Controller_Executer
{
  /**
   * create controller instance
   *
   * @return a subclass instance of Sabel_Controller_Page
   */
  public function create();
  
  /**
   * set an instance of destination
   *
   * @access public
   * @param Sabel_Destination $destination
   * @throws Sabel_Exception_Runtime
   */
  public function setDestination($destination);

  /**
   * execute an action.
   *
   * @param Sabel_Request $request
   * @param Sabel_Storage $storage
   * @return void
   */
  public function execute($request, $storage);
}
