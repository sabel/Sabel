<dl>
<?php foreach ($columns as $column) : ?>
<?php if ($column->increment) continue ?>
  <dt><#php echo $<?php echo $formName ?>->name("<?php echo $column->name ?>") #></dt>
<?php if ($column->isText()) : ?>
  <dd><#php nl2br(h($<?php echo $formName ?>-><?php echo $column->name ?>)) #></dd>
<?php elseif ($column->isBool()) : ?>
  <dd><#php echo ($<?php echo $formName ?>-><?php echo $column->name ?>) ? "on" : "off" #></dd>
<?php else : ?>
  <dd><#php echo h($<?php echo $formName ?>-><?php echo $column->name ?>) #></dd>
<?php endif ?>
<?php endforeach ?>
</dl>

<form style="display: inline;" action="<#php echo uri("a: {$postAction}") #>" method="get">
  <input type="hidden" name="token" value="<#php echo $token #>" />
  <input type="submit" value="ok" />
</form>
<form style="display: inline;" action="<#php echo uri("a: {$correctAction}") #>" method="get">
  <input type="hidden" name="token" value="<#php echo $token #>" />
  <input type="submit" value="correct" />
</form>
