<h2><?php echo $mdlName ?> List</h2>

<p>
  <a href="{"a: prepareCreate"|uri}">&gt;&gt; New <?php echo $mdlName ?></a>
</p>

{if $paginate->results}
<partial name="pager" />

<form action="{"a: confirmDelete"|uri}" method="post">
<table class="sbl_basicTable">
  <tr>
<?php foreach ($columns as $column) : ?>
<?php if ($column->isDate() || $column->isDatetime()) : ?>
    <th><a href="{"a: lists"|uri}?<?php echo $column->name ?>=asc"><img src="{"images/orderAsc.gif"|linkto}" /></a>&nbsp;<?php echo $column->name ?>&nbsp;<a href="{"a: lists"|uri}?<?php echo $column->name ?>=desc"><img src="{"images/orderDesc.gif"|linkto}" /></a></th>
<?php else : ?>
    <th><?php echo $column->name ?></th>
<?php endif ?>
<?php endforeach ?>
    <th>&nbsp;</th>
  </tr>
{foreach from=$paginate->results item=<?php echo lcfirst($mdlName) ?>}
  <tr>
<?php foreach ($columns as $column) : ?>
<?php $mdlcol = '$' . lcfirst($mdlName) . '->' . $column->name ?>
<?php if ($column->isText()) : ?>
    <td>{<?php echo $mdlcol ?>|h|nl2br}</td>
<?php elseif ($column->isBool()) : ?>
    {if <?php echo $mdlcol ?>}
    <td>on</td>
    {else}
    <td>off</td>
    {/if}
<?php else : ?>
<?php if ($column->primary) : ?>
    <td><a href="{"a: prepareEdit"|uri}?<?php echo $column->name ?>={<?php echo $mdlcol ?>|h}">{<?php echo $mdlcol ?>|h}</a></td>
<?php else : ?>
    <td>{<?php echo $mdlcol ?>|h}</td>
<?php endif ?>
<?php endif ?>
<?php endforeach ?>
<?php foreach ($columns as $column) : ?>
<?php if ($column->primary) : ?>
    <td><input type="checkbox" name="ids[]" value="{$<?php echo lcfirst($mdlName) ?>-><?php echo $column->name ?>|h}" /></td>
<?php endif ?>
<?php endforeach ?>
  </tr>
{/foreach}
  <tr>
    <td colspan="<?php echo count($columns) + 1 ?>"><input type="submit" value="Delete selected items" /></td>
  </tr>
</table>
</form>

<partial name="pager" />
{/if}
