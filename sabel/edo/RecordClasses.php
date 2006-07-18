<?php

uses('sabel.edo.RecordObject');

abstract class BaseUserRecordObject extends Sabel_Edo_RecordObject
{
  protected $myChildren         = null;
  protected $myChildConstraints = array();

  public function __construct($param1 = null, $param2 = null)
  {
    $this->setEDO('user', 'pdo');
    parent::__construct($param1, $param2);
  }

  public function getMyChildren()
  {
    return $this->myChildren;
  }

  public function getMyChildConstraint()
  {
    return $this->myChildConstraints;
  }
}

abstract class BaseMailRecordObject extends Sabel_Edo_RecordObject
{

}

// for unit-test
class Test extends BaseUserRecordObject
{
  protected $selectType = Sabel_Edo_RecordObject::WITH_PARENT_OBJECT;

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
  protected $myChildren = array('customer_order','customer_telephone');
  protected $defaultChildConstraints = array('limit' => 10); // (for telephone)

  public function __construct($param1 = null, $param2 = null)
  {
    $this->myChildConstraints['customer_order'] = array('limit' => 10);
    parent::__construct($param1, $param2);
  }
}

class Customer_Order extends BaseUserRecordObject
{
  protected $myChildren = 'order_line';
  //protected $defaultChildConstraints = array('limit' => 10);
  //protected $myChildConstraints = array('limit' => 10);
  public function __construct($param1 = null, $param2 = null)
  {
    //$this->myChildConstraints['order_line'] = array('limit' => 10);
    $this->myChildConstraints['order_line'] = array('limit' => 10);
    parent::__construct($param1, $param2);
  }
}

class Child_Record extends BaseUserRecordObject
{
  public function __construct($table = null)
  {
    parent::__construct();

    if (!is_null($table)) $this->table = $table;
  }
}

class Common_Record extends Sabel_Edo_RecordObject
{
  public function __construct($table = null)
  {
    $this->setEDO('user', 'pdo');
    parent::__construct();

    if (!is_null($table)) $this->table = $table;
  }
}

?>
