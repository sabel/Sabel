<?php

class SabelException extends Exception
{
  public function exceptedFile()
  {
    
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

?>