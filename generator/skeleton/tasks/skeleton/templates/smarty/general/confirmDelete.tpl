<h2>Delete <?php echo $mdlName ?></h2>

<form action="{"a: doDelete"|uri}" method="post">
  <table class="sbl_basicTable">
    <tr>
<?php foreach ($columns as $column) : ?>
      <th><?php echo $column->name ?></th>
<?php endforeach ?>
    </tr>
    {foreach from=$deleteItems item=item}
    <tr>
<?php foreach ($columns as $column) : ?>
<?php $mdlcol = '$item->' . $column->name ?>
<?php if ($column->isString()) : ?>
      <td>{<?php echo $mdlcol ?>|h}</td>
<?php elseif ($column->isText()) : ?>
      <td>{<?php echo $mdlcol ?>|h|nl2br}</td>
<?php elseif ($column->isBool()) : ?>
      {if <?php echo $mdlcol ?>}
      <td>on</td>
      {else}
      <td>off</td>
      {/if}
<?php else : ?>
      <td>{<?php echo $mdlcol ?>}</td>
<?php endif ?>
<?php endforeach ?>
    </tr>
    {/foreach}
  </table>
  <div style="margin-top: 6px;">
    {foreach from=$ids item=id}
      <input type="hidden" name="ids[]" value="{$id}" />
    {/foreach}
    <input type="hidden" name="SBL_CLIENT_ID" value="{$token}" />
    <input style="width: 120px;" type="submit" value="delete" />
  </div>
</form>
