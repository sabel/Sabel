<?php

/**
 * Logger plugin
 *
 * @category   Plugin
 * @package    org.sabel.controller.plugin
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Controller_Plugin_Logger extends Sabel_Controller_Page_Plugin
{
  /**
   * log method
   *
   * @param string $message message for log
   */
  public function log($message)
  {
    $logger = load("Sabel_Logger_File", array("singleton" => true));
    $logger->log($message);
  }
}
