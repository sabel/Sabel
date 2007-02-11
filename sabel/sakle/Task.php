<?php

/**
 * Sabel_Sakle_Task
 *
 * @category   Sakle
 * @package    org.sabel.Sakle
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Sakle_Task
{
  const MSG_INFO = 0x01;
  const MSG_WARN = 0x05;
  const MSG_ERR  = 0x0A;
  
  private $messageHeaders = array(self::MSG_INFO => "[\x1b[1;32mSUCCESS\x1b[m]",
                                  self::MSG_WARN => "[\x1b[1;33mWARNING\x1b[m]",
                                  self::MSG_ERR  => "[\x1b[1;31mERROR\x1b[m]");
                                    
  abstract public function run($arguments);
  
  protected function printMessage($msg, $type = self::MSG_INFO)
  {
    switch ($type) {
      case self::MSG_INFO:
        echo $this->messageHeaders[self::MSG_INFO] .': '. $msg . "\n";
        break;
      case self::MSG_WARN:
        echo $this->messageHeaders[self::MSG_WARN] .': '. $msg . "\n";
        break;
      case self::MSG_ERR:
        echo $this->messageHeaders[self::MSG_ERR]  .': '. $msg . "\n";
        break;
    }
  }
}