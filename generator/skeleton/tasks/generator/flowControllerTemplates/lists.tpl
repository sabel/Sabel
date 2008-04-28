<h2><?php echo $mdlName ?> List</h2>

<# if ($paginate->results) : #>
<p>
  <a href="<#= uri("a: prepareCreate") #>">&gt;&gt; New <?php echo $mdlName ?></a>
</p>

<partial name="pager" />

<table class="sbl_basicTable">
  <tr>
<?php foreach ($columns as $column) : ?>
    <th><?php echo $column->name ?></th>
<?php endforeach ?>
  </tr>
<# foreach ($paginate->results as $<?php echo lcfirst($mdlName) ?>) : #>
  <tr>
<?php foreach ($columns as $column) : ?>
<?php if ($column->isString()) : ?>
<?php if ($column->primary) : ?>
    <td><a href="<#= uri("a: prepareEdit") #>?<?php echo $column->name ?>=<#= $<?php echo lcfirst($mdlName) ?>-><?php echo $column->name ?>|h #>"><#= $<?php echo lcfirst($mdlName) ?>-><?php echo $column->name ?>|h #></a></td>
<?php else : ?>
    <td><#= $<?php echo lcfirst($mdlName) ?>-><?php echo $column->name ?>|h #></td>
<?php endif ?>
<?php elseif ($column->isText()) : ?>
    <td><#= $<?php echo lcfirst($mdlName) ?>-><?php echo $column->name ?>|h|nl2br #></td>
<?php elseif ($column->isBool()) : ?>
    <td><# echo ($<?php echo lcfirst($mdlName) ?>-><?php echo $column->name ?>) ? "true" : "false" #></td>
<?php else : ?>
<?php if ($column->primary) : ?>
    <td><a href="<#= uri("a: prepareEdit") #>?<?php echo $column->name ?>=<#= $<?php echo lcfirst($mdlName) ?>-><?php echo $column->name ?> #>"><#= $<?php echo lcfirst($mdlName) ?>-><?php echo $column->name ?> #></a></td>
<?php else : ?>
    <td><#= $<?php echo lcfirst($mdlName) ?>-><?php echo $column->name ?> #></td>
<?php endif ?>
<?php endif ?>
<?php endforeach ?>
  </tr>
<# endforeach #>
</table>

<partial name="pager" />
<# endif #>
