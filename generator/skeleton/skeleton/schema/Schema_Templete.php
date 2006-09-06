<#php

class <?php echo $className ?> 
{
  public function getParsedSQL()
  {
    $sql = array();

<?php foreach ($schema as $cName => $line) : ?>
    $sql['<?php echo $cName?>'] = '<?php echo $line?>';
<?php endforeach ?>

    return $sql;
  }
}
