<#php
<?php $lwClassName = strtolower($className) ?>

/**
 * there belows are annotation of Templates
 *
 */
class <?php echo $className ?> extends Sabel_DB_Model
{
  // do you want to play with parent? ;-) try this.
  protected $withParent = false;
}