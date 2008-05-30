<h2>Edit <?php echo $mdlName ?></h2>

<#php echo $this->partial("error", array("errors" => $<?php echo $formName ?>->getErrors())) #>

<#php echo $<?php echo $formName ?>->open("a: edit") #>
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
    <input type="hidden" name="SBL_CLIENT_ID" value="<#php echo $token #>" />
    <input type="hidden" name="<?php echo $primaryColumn ?>" value="<#php echo $<?php echo $primaryColumn ?> #>" />
    <input type="submit" value="edit" />
  </div>
<#php echo $<?php echo $formName ?>->close() #>
