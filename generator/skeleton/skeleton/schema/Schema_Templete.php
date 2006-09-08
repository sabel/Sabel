<#php

class <?php echo $className ?> 
{
  public function get()
  {
    $sql = array();

<?php foreach ($schema as $line) : ?>
    <?php echo $line ?>
<?php endforeach ?>

    return $sql;
  }
}
