<h2>Delete <?php echo $mdlName ?></h2>

<form action="<#= uri("a: doDelete") #>" method="post">
  <table class="sbl_basicTable">
    <tr>
<?php foreach ($columns as $column) : ?>
      <th><?php echo $column->name ?></th>
<?php endforeach ?>
    </tr>
    <# foreach ($deleteItems as $item) : #>
    <tr>
<?php foreach ($columns as $column) : ?>
<?php $mdlcol = '$item->' . $column->name ?>
<?php if ($column->isText()) : ?>
      <td><#n <?php echo $mdlcol ?> #></td>
<?php elseif ($column->isBool()) : ?>
      <td><# echo (<?php echo $mdlcol ?>) ? "on" : "off" #></td>
<?php else : ?>
      <td><#= <?php echo $mdlcol ?> #></td>
<?php endif ?>
<?php endforeach ?>
    </tr>
    <# endforeach #>
  </table>
  <div style="margin-top: 6px;">
    <token value="<#= $token #>" />
    <input style="width: 120px;" type="submit" value="delete" />
  </div>
</form>
