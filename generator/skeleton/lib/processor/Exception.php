<?php

/**
 * Processor_Exeception
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Exception extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $this->response = $this->controller->getResponse();
    
    if ($this->response->isServerError()) {
      $exception = Sabel_Context::getContext()->getException();
      if (!is_object($exception)) return;
      
      $eol = $this->getEol();
      $msg = "Exception Message: " . $exception->getMessage() . $eol
           . "At: " . date("r") . $eol . $eol
           . $this->getReadableTrace($exception->getTrace(), $eol);
           
      $this->exception($exception, $msg);
    }
  }
  
  protected function getReadableTrace($traces, $eol)
  {
    $result = array();
    
    foreach ($traces as $line) {
      $trace = array();
      
      if (isset($line["file"])) {
        $trace[] = "FILE: {$line["file"]}({$line["line"]})";
      } else {
        $trace[] = "FILE: Unknown";
      }
      
      $args = array();
      if (isset($line["args"]) && !empty($line["args"])) {
        foreach ($line["args"] as $arg) {
          if (is_object($arg)) {
            $args[] = "(Object)" . get_class($arg);
          } elseif (is_bool($arg)) {
            $str = ($arg) ? "true" : "false";
            $args[] = "(Boolean)" . $str;
          } elseif (is_resource($arg)) {
            $args[] = "(Resource)" . get_resource_type($arg);
          } elseif ($arg === null) {
            $args[] = "null";
          } else {
            $args[] = "(" . ucfirst(gettype($arg)) . ")" . $arg;
          }
        }
      }
      
      $args = implode(", ", $args);
      
      if (isset($line["class"])) {
        $trace[] = "CALL: " . $line["class"]
                 . $line["type"] . $line["function"] . "({$args})";
      } else {
        $trace[] = "FUNCTION: " . $line["function"] . "({$args})";
      }
      
      $result[] = implode($eol, $trace);
    }
    
    return implode($eol . $eol, $result);
  }
  
  private function exception($exception, $message)
  {
    if (ENVIRONMENT === PRODUCTION) {
      if ($exception instanceof Sabel_Exception_Runtime) {
        $exception->writeSyslog($message);
      }
      
      // send mail. etc.
      
    } else {
      $this->response->setResponse("exception_message", $message);
    }
  }
  
  private function getEol()
  {
    return (ENVIRONMENT === DEVELOPMENT) ? "<br/>" : "\r\n";
  }
}
