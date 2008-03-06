<?php

/**
 * Sabel_Logger_Interface
 *
 * @interface
 * @category   Logger
 * @package    org.sabel.logger
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
interface Sabel_Logger_Interface
{
  /**
   * @param string $filePath
   * @param array  $messages
   *
   * @return void
   */
  public function output($filePath, $messages);
}
