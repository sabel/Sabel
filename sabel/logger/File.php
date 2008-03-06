<?php

/**
 * Sabel_Logger_File
 *
 * @category   Logger
 * @package    org.sabel.logger
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Logger_File extends Sabel_Object implements Sabel_Logger_Interface
{
  public function output($filePath, $messages)
  {
    if (empty($messages)) return;
    
    $fp  = fopen($filePath, "a");
    $sep = "============================================================" . PHP_EOL;
    
    fwrite($fp, PHP_EOL . $sep . PHP_EOL);
    fwrite($fp, implode(PHP_EOL, $messages) . PHP_EOL);
    fclose($fp);
  }
}
