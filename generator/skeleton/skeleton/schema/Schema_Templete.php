<#php

class <?php echo $className ?> 
{
  public function get()
  {
    $sql = array();

<?php foreach ($colArray as $line) : ?>
    <?php echo $line ?>
<?php endforeach ?>

    return $sql;
  }
}
