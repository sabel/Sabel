<h2><?php echo $mdlName ?> List</h2>

<p>
  <a href="<#php echo uri("a: prepareCreate") #>">&gt;&gt; New <?php echo $mdlName ?></a>
</p>

<#php if ($paginate->results) : #>
<#php echo $renderer->partial("pager") #>

<form action="<#php echo uri("a: confirmDelete") #>" method="post">
<table class="sbl_basicTable">
  <tr>
<?php foreach ($columns as $column) : ?>
<?php if ($column->isDate() || $column->isDatetime()) : ?>
    <th><a href="<#php echo uri("a: lists") #>?<?php echo $column->name ?>=asc"><img src="<#php echo linkto("images/orderAsc.gif") #>" /></a>&nbsp;<?php echo $column->name ?>&nbsp;<a href="<#php echo uri("a: lists") #>?<?php echo $column->name ?>=desc"><img src="<#php echo linkto("images/orderDesc.gif") #>" /></a></th>
<?php else : ?>
    <th><?php echo $column->name ?></th>
<?php endif ?>
<?php endforeach ?>
    <th>&nbsp;</th>
  </tr>
<#php foreach ($paginate->results as $<?php echo lcfirst($mdlName) ?>) : #>
  <tr>
<?php foreach ($columns as $column) : ?>
<?php $mdlcol = '$' . lcfirst($mdlName) . '->' . $column->name ?>
<?php if ($column->isText()) : ?>
    <td><#php echo nl2br(h(<?php echo $mdlcol ?>)) #></td>
<?php elseif ($column->isBool()) : ?>
    <td><#php echo (<?php echo $mdlcol ?>) ? "on" : "off" #></td>
<?php else : ?>
<?php if ($column->primary) : ?>
    <td><a href="<#php echo uri("a: prepareEdit") #>?<?php echo $column->name ?>=<#php echo h(<?php echo $mdlcol ?>) #>"><#php echo h(<?php echo $mdlcol ?>) #></a></td>
<?php else : ?>
    <td><#php echo h(<?php echo $mdlcol ?>) #></td>
<?php endif ?>
<?php endif ?>
<?php endforeach ?>
<?php foreach ($columns as $column) : ?>
<?php if ($column->primary) : ?>
    <td><input type="checkbox" name="ids[]" value="<#php echo h($<?php echo lcfirst($mdlName) ?>-><?php echo $column->name ?>) #>" /></td>
<?php endif ?>
<?php endforeach ?>
  </tr>
<#php endforeach #>
  <tr>
    <td colspan="<?php echo count($columns) + 1 ?>"><input type="submit" value="Delete selected items" /></td>
  </tr>
</table>
</form>

<#php echo $renderer->partial("pager") #>
<#php endif #>
