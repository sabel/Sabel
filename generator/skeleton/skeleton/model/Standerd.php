<#php

class <?php echo $className ?> extends Sabel_DB_Model
{
  public function __construct($param1 = null, $param2 = null)
  {
    $this->setDriver('default');
    parent::__construct($param1, $param2);
  }
  
  public function show($id)
  {
    return $this->selectOne($id);
  }
  
  public function lists()
  {
    return $this->select();
  }
}