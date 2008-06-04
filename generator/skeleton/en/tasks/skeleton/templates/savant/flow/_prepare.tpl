<#php echo $renderer->partial("error", array("errors" => $<?php echo $formName ?>->getErrors())) #>

<#php echo $<?php echo $formName ?>->open("a: {$confirmAction}") #>
  <dl>
<?php foreach ($columns as $column) : ?>
<?php if ($column->increment) continue ?>
    <dt><#php echo $<?php echo $formName ?>->mark("<?php echo $column->name ?>") #></dt>
<?php if ($column->isDatetime()) : ?>
    <dd><#php echo $<?php echo $formName ?>->datetime("<?php echo $column->name ?>") #></dd>
<?php elseif ($column->isDate()) : ?>
    <dd><#php echo $<?php echo $formName ?>->date("<?php echo $column->name ?>") #></dd>
<?php elseif ($column->isText()) : ?>
    <dd><#php echo $<?php echo $formName ?>->textarea("<?php echo $column->name ?>") #></dd>
<?php elseif ($column->isBool()) : ?>
    <dd><#php echo $<?php echo $formName ?>->radio("<?php echo $column->name ?>", array(1 => "on", 0 => "off")) #></dd>
<?php else : ?>
    <dd><#php echo $<?php echo $formName ?>->text("<?php echo $column->name ?>") #></dd>
<?php endif ?>
<?php endforeach ?>
  </dl>
  <div>
    <input type="hidden" name="token" value="<#php echo $token #>" />
    <input type="submit" value="confirm" />
  </div>
<#php echo $<?php echo $formName ?>->close() #>
