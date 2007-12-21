<h2>
  Table: <?= $table ?>
</h2>

<h3>
  <a href="<?= uri("c: main, a: index") ?>">back to table list.</a>
</h3>

<? if ($paginate->results) : ?>
  
  <table border="1">
    <tr>
    <? foreach($columns as $column) : ?>
      <th><?= $column ?></th>
    <? endforeach ?>
      <th>&nbsp;</th>
    </tr>
  
    <? foreach ($paginate->results as $model) : ?>
    <tr>
      <? foreach ($columns as $column) : ?>
      <? if ($column === $pkey) : ?>
        <td nowrap>
          <a href="<?= uri("a: edit, param: {$table}") ?>?id=<?= $model->$column ?>">
            <?= $model->$column ?>
          </a>
        </td>
      <? else : ?>
        <td nowrap><?= $model->$column ?></td>
      <? endif ?>
      <? endforeach ?>
      <td>
        <input type="button" value="delete" onclick="deleteItem('<?= $model->$pkey ?>')" />
      </td>
    </tr>
    <? endforeach ?>
  </table>
  
  <br/>
  
  <?= $this->partial("pager") ?>
  
<? else : ?>
  empty set.
<? endif ?>

<script type="text/javascript">

var table = "<?= $table ?>";

function deleteItem(id)
{
  var message = "delete item. ok?";
  
  if (window.confirm(message)) {
    var Uri = new Sabel.Util.Uri();
    var url = "http://" + Uri.domain + "<?= uri('a: delete') ?>/" + table + "?id=" + id;
    
    window.location.href = url;
  }
}

</script>
