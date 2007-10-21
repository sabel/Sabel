<?php

/**
 * Sabel_Logger_Interface
 *
 * @package    org.sabel.logger
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
interface Sabel_Logger_Interface
{
  public function log($text, $level = LOG_INFO);
}