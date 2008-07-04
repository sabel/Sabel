<dl>
<?php foreach ($columns as $column) : ?>
<?php if ($column->increment || $column->name === $versionColumn) continue ?>
  <dt>{$<?php echo $formName ?>->name("<?php echo $column->name ?>")}</dt>
<?php if ($column->isText()) : ?>
  <dd>{$<?php echo $formName ?>-><?php echo $column->name ?>|h|nl2br}</dd>
<?php elseif ($column->isBool()) : ?>
  {if $<?php echo $formName ?>-><?php echo $column->name ?>}
  <dd>on</dd>
  {else}
  <dd>off</dd>
  {/if}
<?php else : ?>
  <dd>{$<?php echo $formName ?>-><?php echo $column->name ?>|h}</dd>
<?php endif ?>
<?php endforeach ?>
</dl>

<form style="display: inline;" action="{"a: $postAction"|uri}" method="get">
  <input type="hidden" name="token" value="{$token}" />
  <input type="submit" value="ok" />
</form>
<form style="display: inline;" action="{"a: $correctAction"|uri}" method="get">
  <input type="hidden" name="token" value="{$token}" />
  <input type="submit" value="correct" />
</form>
