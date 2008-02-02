<?php

/**
 * Sabel_Exception_Runtime
 *
 * @category   Exception
 * @package    org.sabel.exception
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2002-2006 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Exception_Runtime extends Exception
{
  public function writeSyslog($message)
  {
    if (Sabel_Environment::create()->isWin()) {
      openlog("sabel-exception", LOG_PID, LOG_USER);
    } else {
      openlog("sabel-exception", LOG_PID, LOG_LOCAL0);
    }
    
    $message = str_replace(array("\r\n", "\r"), "\n", $message);
    $lines = explode("\n", $message);
    
    foreach ($lines as $line) {
      syslog(LOG_WARNING, $line);
    }
    
    closelog();
    
    return $lines;
  }
}
