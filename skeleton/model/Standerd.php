<#php

class <?php echo $className ?> extends Sabel_DB_Mapper
{
  public function __construct($param1 = null, $param2 = null)
  {
    $this->setDriver('default');
    parent::__construct($param1, $param2);
  }
  
  public function show()
  {
  }
  
  public function lists()
  {
  }
  
  public function delete()
  {
  }
  
  public function edit()
  {
  }
}
