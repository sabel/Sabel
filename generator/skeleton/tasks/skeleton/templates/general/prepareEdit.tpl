<h2>Edit <?php echo $mdlName ?></h2>

<formerr form="<?php echo lcfirst($mdlName) ?>" />

<#= $<?php echo $formName ?>->open("a: edit") #>
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
    <dd><#= $<?php echo $formName ?>->radio("<?php echo $column->name ?>", array(1 => "on", 0 => "off")) #></dd>
<?php else : ?>
    <dd><#= $<?php echo $formName ?>->text("<?php echo $column->name ?>") #></dd>
<?php endif ?>
<?php endforeach ?>
  </dl>
  <div>
    <token value="<#= $token #>" />
    <input type="hidden" name="<?php echo $primaryColumn ?>" value="<#= $<?php echo $primaryColumn ?> #>" />
    <input type="submit" value="edit" />
  </div>
<#= $<?php echo $formName ?>->close() #>
