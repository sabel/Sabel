<formerr form="<?php echo lcfirst($mdlName) ?>" />

<#e $<?php echo $formName ?>->open("a: {$confirmAction}") #>
  <dl>
<?php foreach ($columns as $column) : ?>
<?php if ($column->increment || $column->name === $versionColumn) continue ?>
    <dt><#e $<?php echo $formName ?>->mark("<?php echo $column->name ?>") #></dt>
<?php if ($column->isDatetime()) : ?>
    <dd><#e $<?php echo $formName ?>->datetime("<?php echo $column->name ?>") #></dd>
<?php elseif ($column->isDate()) : ?>
    <dd><#e $<?php echo $formName ?>->date("<?php echo $column->name ?>") #></dd>
<?php elseif ($column->isText()) : ?>
    <dd><#e $<?php echo $formName ?>->textarea("<?php echo $column->name ?>") #></dd>
<?php elseif ($column->isBool()) : ?>
    <dd><#e $<?php echo $formName ?>->radio("<?php echo $column->name ?>", array(1 => "on", 0 => "off")) #></dd>
<?php else : ?>
    <dd><#e $<?php echo $formName ?>->text("<?php echo $column->name ?>") #></dd>
<?php endif ?>
<?php endforeach ?>
  </dl>
  <div>
    <token value="<#= $token #>" />
    <input type="submit" value="confirm" />
  </div>
<#e $<?php echo $formName ?>->close() #>
