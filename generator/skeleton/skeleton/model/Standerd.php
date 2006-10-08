<#php

<?php $lwClassName = strtolower($className) ?>

/**
 * @assign choice to <? echo $lwClassName ?>
 * @assign assign to <? echo $lwClassName ?>
 * @assign select to <? echo $lwClassName ?>s
 */
class <?php echo $className ?> extends Sabel_DB_Model
{
  public function __construct($param1 = null, $param2 = null)
  {
    $this->setDriver('default');
    $this->enableParent();
    parent::__construct($param1, $param2);
  }
  
  public function assign()
  {
    return $this;
  }
}