<formerr form="<?php echo lcfirst($mdlName) ?>" />

<#= $<?php echo $formName ?>->open("a: {$confirmAction}") #>
  <dl>
<?php foreach ($columns as $column) : ?>
<?php if ($column->increment) continue ?>
    <dt><#= $<?php echo $formName ?>->mark("<?php echo $column->name ?>") #></dt>
<?php if ($column->isDatetime()) : ?>
    <dd><#= $<?php echo $formName ?>->datetime("<?php echo $column->name ?>") #></dd>
<?php elseif ($column->isDate()) : ?>
    <dd><#= $<?php echo $formName ?>->date("<?php echo $column->name ?>") #></dd>
<?php elseif ($column->isText()) : ?>
    <dd><#= $<?php echo $formName ?>->textarea("<?php echo $column->name ?>") #></dd>
<?php elseif ($column->isBool()) : ?>
    <dd><#= $<?php echo $formName ?>->radio("<?php echo $column->name ?>", array(0 => "on", 1 => "off")) #></dd>
<?php else : ?>
    <dd><#= $<?php echo $formName ?>->text("<?php echo $column->name ?>") #></dd>
<?php endif ?>
<?php endforeach ?>
  </dl>
  <div>
    <token value="<#= $token #>" />
    <input type="submit" value="confirm" />
  </div>
<#= $<?php echo $formName ?>->close() #>
