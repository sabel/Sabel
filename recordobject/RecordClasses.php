<?php

require_once('RecordObject.php');

class Child_Record extends RecordObject
{

}

class Test extends RecordObject
{
  public function getCondition()
  {
    return $this->conditions;
  }

  public function unsetCondition()
  {
    $this->conditions = array();
  }
}

class Common_Record extends RecordObject
{
  public function __construct($table = null)
  {
    parent::__construct();

    if (!is_null($table))
      $this->setTable($table);
  }
}

?>
