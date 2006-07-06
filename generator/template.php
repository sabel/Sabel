[?php

class @@ACTION_NAME@@ extends SabelPageController
{
  public function index()
  {
    // @TODO implemented.
  }
  
  public function show()
  {
    
  }
  
  public function delete()
  {
    
  }
  
<? foreach ($this->methods as $key => $value) { ?>
  public function show<?echo ucfirst($value);?>()
  {
    // @todo implement.
    throw new Exception("not implemented yet");
  }
  
  public function delete<?echo ucfirst($value);?>()
  {
    // @todo implement.
    throw new Exception("not implemented yet");
  }
  
  public function update<?echo ucfirst($value);?>()
  {
    // @todo implement.
    throw new Exception("not implemented yet");
  }
  
<? } ?>
}

?]
