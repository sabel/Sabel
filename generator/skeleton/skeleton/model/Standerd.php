<#php
<?php $lwClassName = strtolower($className) ?>

/**
 * there belows are annotation of Templates
 *
 * @assign select to <? echo $lwClassName ?>s
 * @assign selectOne to <? echo $lwClassName ?> 
 */
class <?php echo $className ?> extends Sabel_DB_Mapper
{
  // do you want to play with parent? ;-) try this.
  protected $withParent = false;
}