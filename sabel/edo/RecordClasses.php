<?php
/*
require_once('RecordObject.php');

abstract class BaseUserRecordObject extends RecordObject
{
  protected $myChildren = null;

  public function __construct($param1 = null, $param2 = null)
  {
    $this->setEDO('user', 'pdo');
    parent::__construct($param1, $param2);
  }

  public function getMyChildren()
  {
    return $this->myChildren;
  }
}

abstract class BaseMailRecordObject extends RecordObject
{

}

// for unit-test
class Test extends BaseUserRecordObject
{
  public function __construct($param1 = null, $param2 = null)
  {
    $this->selectType = RecordObject::WITH_PARENT_OBJECT;
    parent::__construct($param1, $param2);
  }

  public function getCondition()
  {
    return $this->conditions;
  }

  public function unsetCondition()
  {
    $this->conditions = array();
  }

  public function getData()
  {
    return $this->data;
  }
}

class Customer extends BaseUserRecordObject
{
  protected $myChildren = 'customer_order';
  protected $childConstraints = array('limit' => 10);
}

class Customer_Order extends BaseUserRecordObject
{
  protected $myChildren = 'order_line';
  protected $childConstraints = array('limit' => 10);
}

class Child_Record extends BaseUserRecordObject
{
  public function __construct($table = null)
  {
    parent::__construct();

    if (!is_null($table)) $this->table = $table;
  }
}

class Common_Record extends RecordObject
{
  public function __construct($table = null)
  {
    $this->setEDO('user', 'pdo');
    parent::__construct();

    if (!is_null($table)) $this->table = $table;
  }
}
*/
?>
