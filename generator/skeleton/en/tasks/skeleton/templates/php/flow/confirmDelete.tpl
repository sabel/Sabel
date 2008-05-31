<h2>Delete <?php echo $mdlName ?></h2>

<form action="<#php echo uri("a: doDelete") #>" method="post">
  <table class="sbl_basicTable">
    <tr>
<?php foreach ($columns as $column) : ?>
      <th><?php echo $column->name ?></th>
<?php endforeach ?>
    </tr>
    <#php foreach ($deleteItems as $item) : #>
    <tr>
<?php foreach ($columns as $column) : ?>
<?php $mdlcol = '$item->' . $column->name ?>
<?php if ($column->isText()) : ?>
      <td><#php echo nl2br(h(<?php echo $mdlcol ?>)) #></td>
<?php elseif ($column->isBool()) : ?>
      <td><#php echo (<?php echo $mdlcol ?>) ? "on" : "off" #></td>
<?php else : ?>
      <td><#php echo h(<?php echo $mdlcol ?>) #></td>
<?php endif ?>
<?php endforeach ?>
    </tr>
    <#php endforeach #>
  </table>
  <div style="margin-top: 6px;">
    <input type="hidden" name="token" value="<#php echo $token #>" />
    <input style="width: 120px;" type="submit" value="delete" />
  </div>
</form>
