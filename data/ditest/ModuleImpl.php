<?php

class Data_Ditest_ModuleImpl implements Data_Ditest_Module
{
  /** @test wo　*/
  public function test($a)
  {
    for ($i = 0; $i < 10; $i++) {
      $this->internalCall();
    }
    
    return "ModuleImpl result.";
  }
  
  public function __get($key)
  {
    
  }
  
  /**
   * this is class name.
   *
   * @injection MockInjection
   * @injection RecordRunningTimeInjection
   */
  public function returnArray()
  {
    $arrays = array();
    for ($i = 0; $i < 10; $i++) {
      $arrays[] = array('test'=>'test', 'test2'=>'test', 'test3'=>var_export($this, 1));
    }
    return $arrays;
  }
  
  protected function internalCall()
  {
    $a = 0;
  }
}

?>