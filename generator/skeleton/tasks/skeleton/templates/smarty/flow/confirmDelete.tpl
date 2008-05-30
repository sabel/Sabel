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
<?php if ($column->isText()) : ?>
      <td>{<?php echo $mdlcol ?>|h|nl2br}</td>
<?php elseif ($column->isBool()) : ?>
      {if <?php echo $mdlcol ?>}
      <td>on</td>
      {else}
      <td>off</td>
      {/if}
<?php else : ?>
      <td>{<?php echo $mdlcol ?>|h}</td>
<?php endif ?>
<?php endforeach ?>
    </tr>
    {/foreach}
  </table>
  <div style="margin-top: 6px;">
    <input type="hidden" name="token" value="{$token}" />
    <input style="width: 120px;" type="submit" value="delete" />
  </div>
</form>
