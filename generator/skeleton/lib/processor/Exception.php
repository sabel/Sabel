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
  private $response = null;
  
  public function execute($bus)
  {
    $this->response = $bus->get("response");
    
    if ($this->response->isServerError()) {
      $exception = Sabel_Context::getContext()->getException();
      if (!is_object($exception)) return true;
      
      $eol = $this->getEol();
      $msg = "ERROR: " . $exception->getMessage() . $eol . $eol
           . $this->getReadableTrace($exception->getTrace(), $eol);
           
      $this->exception($msg);
    }
    
    return true;
  }
  
  protected function getReadableTrace($traces, $eol)
  {
    $result = array();
    
    foreach ($traces as $line) {
      $trace = array();
      $trace[] = "FILE: {$line["file"]}({$line["line"]})";
      
      if (isset($line["class"])) {
        $trace[] = "CALL: " . $line["class"]
                 . $line["type"] . $line["function"] . "()";
      } else {
        $trace[] = "FUNCTION: " . $line["function"] . "()";
      }
      
      $result[] = implode($eol, $trace);
    }
    
    return implode($eol . $eol, $result);
  }
  
  private function exception($message)
  {
    if (ENVIRONMENT === PRODUCTION) {
      
    } else {
      $this->response->setResponse("exception_message", $message);
    }
  }
  
  private function getEol()
  {
    return (ENVIRONMENT === DEVELOPMENT) ? "<br/>" : "\n";
  }
}
