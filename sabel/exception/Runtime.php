<?php

/**
 * Sabel_Exception_Runtime
 *
 * @category   Exception
 * @package    org.sabel.exception
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Exception_Runtime extends Exception
{
  public function flightLog()
  {
    $file = $this->getFile();
    $code = $this->getCode();
    $line = $this->getLine();
    $msg  = $this->getMessage();
    $traceRaw = $this->getTrace();
    
    for ($i = 0, $c = count($traceRaw); $i < $c; $i++) {
      if (!isset($traceRaw[$i]['file'])) continue;
      
      $stFile = $traceRaw[$i]['file'];
      $stLine = $traceRaw[$i]['line'];
      $stFunction = $traceRaw[$i]['function'];
      
      $trace[] = sprintf('at %s in [%s:%s]', $stFunction, $stFile, $stLine);
    }
    
    $format = 'Exception: %s from "%s" on "%s"' . "\n";
    $errorMsg = sprintf($format, $msg, $file, $line);
    
    foreach ($trace as $l) {
      $errorMsg .= $l . "\n";
    }
    
    $time = date('D d H:i:s');
    if (!$this->writeFile($time.': '.$errorMsg)) $this->writeSyslog($errorMsg);
    exit;
  }
  
  protected function writeFile($msg)
  {
    if (!$fp = @fopen(SabelConst::EXCEPTION_LOG_FILE_NAME, 'a')) return false;
    fwrite($fp, $msg);
    fclose($fp);
    return true;
  }
  
  protected function writeSyslog($msg)
  {
    openlog('SabelErrorLog', LOG_PID | LOG_ERROR, LOG_LOCAL0);
    syslog(LOG_WARNING, $msg);
    closelog();
  }
  
  public function printStackTrace()
  {
    $file = $this->getFile();
    $code = $this->getCode();
    $line = $this->getLine();
    $msg  = $this->getMessage();
    $traceRaw = $this->getTrace();
    
    for ($i = 0, $c = count($traceRaw); $i < $c; $i++) {
      if (!isset($traceRaw[$i]['file'])) continue;
      
      $stFile = $traceRaw[$i]['file'];
      $stLine = $traceRaw[$i]['line'];
      $stFunction = $traceRaw[$i]['function'];
      
      $trace[] = sprintf('at %s in [%s:%s]', $stFunction, $stFile, $stLine);
    }
    
    $format = 'Exception: %s from "%s" on "%s"' . "<br/><br/>\n\n";
    $errorMsg = sprintf($format, $msg, $file, $line);
    
    foreach ($trace as $l) {
      $errorMsg .= $l . "<br/>\n";
    }
    
    print $errorMsg;
  }
}