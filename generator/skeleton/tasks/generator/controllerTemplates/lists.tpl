<h2><?php echo $mdlName ?> List</h2>

<p>
  <a href="<#= uri("a: prepareCreate") #>">&gt;&gt; New <?php echo $mdlName ?></a>
</p>

<# if ($paginate->results) : #>
<partial name="pager" />

<form action="<#= uri("a: confirmDelete") #>" method="post">
<table class="sbl_basicTable">
  <tr>
<?php foreach ($columns as $column) : ?>
<?php if ($column->isDate() || $column->isDatetime()) : ?>
    <th><a href="<#= uri("a: lists") #>?<?php echo $column->name ?>=asc"><img src="<#= linkto("images/orderAsc.gif") #>" /></a>&nbsp;<?php echo $column->name ?>&nbsp;<a href="<#= uri("a: lists") #>?<?php echo $column->name ?>=desc"><img src="<#= linkto("images/orderDesc.gif") #>" /></a></th>
<?php else : ?>
    <th><?php echo $column->name ?></th>
<?php endif ?>
<?php endforeach ?>
    <th>&nbsp;</th>
  </tr>
<# foreach ($paginate->results as $<?php echo lcfirst($mdlName) ?>) : #>
  <tr>
<?php foreach ($columns as $column) : ?>
<?php $mdlcol = '$' . lcfirst($mdlName) . '->' . $column->name ?>
<?php if ($column->isString()) : ?>
<?php if ($column->primary) : ?>
    <td><a href="<#= uri("a: prepareEdit") #>?<?php echo $column->name ?>=<#= <?php echo $mdlcol ?>|h #>"><#= <?php echo $mdlcol ?>|h #></a></td>
<?php else : ?>
    <td><#= <?php echo $mdlcol ?>|h #></td>
<?php endif ?>
<?php elseif ($column->isText()) : ?>
    <td><#= <?php echo $mdlcol ?>|h|nl2br #></td>
<?php elseif ($column->isBool()) : ?>
    <td><# echo (<?php echo $mdlcol ?>) ? "on" : "off" #></td>
<?php else : ?>
<?php if ($column->primary) : ?>
    <td><a href="<#= uri("a: prepareEdit") #>?<?php echo $column->name ?>=<#= <?php echo $mdlcol ?> #>"><#= <?php echo $mdlcol ?> #></a></td>
<?php else : ?>
    <td><#= <?php echo $mdlcol ?> #></td>
<?php endif ?>
<?php endif ?>
<?php endforeach ?>
<?php foreach ($columns as $column) : ?>
<?php if ($column->primary) : ?>
    <td><input type="checkbox" name="ids[]" value="<#= $<?php echo lcfirst($mdlName) ?>-><?php echo $column->name ?> #>" /></td>
<?php endif ?>
<?php endforeach ?>
  </tr>
<# endforeach #>
  <tr>
    <td colspan="<?php echo count($columns) + 1 ?>"><input type="submit" value="Delete selected items" /></td>
  </tr>
</table>
</form>

<partial name="pager" />
<# endif #>
