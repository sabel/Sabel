<dl>
<?php foreach ($columns as $column) : ?>
<?php if ($column->increment) continue ?>
  <dt><#= $<?php echo $formName ?>->name("<?php echo $column->name ?>") #></dt>
<?php if ($column->isText()) : ?>
  <dd><#n $<?php echo $formName ?>-><?php echo $column->name ?> #></dd>
<?php elseif ($column->isBool()) : ?>
  <dd><# echo ($<?php echo $formName ?>-><?php echo $column->name ?>) ? "on" : "off" #></dd>
<?php else : ?>
  <dd><#= $<?php echo $formName ?>-><?php echo $column->name ?> #></dd>
<?php endif ?>
<?php endforeach ?>
</dl>

<form style="display: inline;" action="<#= uri("a: {$postAction}") #>" method="get">
  <token value="<#= $token #>" />
  <input type="submit" value="ok" />
</form>
<form style="display: inline;" action="<#= uri("a: {$correctAction}") #>" method="get">
  <token value="<#= $token #>" />
  <input type="submit" value="correct" />
</form>
