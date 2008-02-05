<?php

class TestProcessor_Initializer extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    // default page title.
    $bus->get("response")->setResponse("pageTitle", "Sabel");
    
    // $this->trim();
  }

  /**
   * strip whitespace from post values.
   */
  private function trim()
  {
    if (!$this->request->isPost()) return;
    
    $func = (extension_loaded("mbstring")) ? "mb_trim" : "trim";
    
    if ($values = $this->request->fetchPostValues()) {
      foreach ($values as &$value) {
        if ($value === null || is_array($value)) continue;
        $result = $func($value);
        $value  = ($result === "") ? null : $result;
      }
      
      $this->request->setPostValues($values);
    }
  }
}
