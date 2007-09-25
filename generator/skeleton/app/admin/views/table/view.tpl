<? if ($models) : ?>
<form action="<?= uri("a: delete, param: {$models[0]->getName()}") ?>" method="POST">
  <table border="1">
    <tr>
      <th><input type="checkbox" onchange="turnover(this.checked)"></th>
      <? foreach ($columns as $column) : ?>
        <th><?= $column ?></th>
      <? endforeach ?>
    </tr>
    <? foreach ($models as $i => $model) : ?>
      <tr onclick="edit(event, '<?= $model->_key ?>')">
        <td>
          <input id="delkey<?= $i ?>" name="delkey[]" type="checkbox" value="<?= $model->_key ?>" />
        </td>
        <? foreach ($model->toArray() as $key => $value) : ?>
          <? if (in_array($key, $columns)) : ?>
            <? $sc = $schema->getColumnByName($key) ?>
            <? if ($sc->isBool()) : ?>
              <td><?= ($value) ? "true" : "false" ?></td>
            <? elseif ($sc->isText()) : ?>
              <td><?= $value|h|nl2br ?></td>
            <? else : ?>
              <td><?= $value|h ?></td>
            <? endif ?>
          <? endif ?>
        <? endforeach ?>
      </tr>
    <? endforeach ?>
  </table>
  <br/>
  <input type="submit" value="delete" />
</form>
<? else : ?>
  no data.<br/>
<? endif ?>

<br/>
<hr size="1" />

<?= $this->partial("error") ?>

<div id="model_form">
  <?= $$formName->start("a: insert, param: {$$formName->getModel()->getName()}") ?>
    <? foreach ($schema->getColumns() as $colName => $column) : ?>
      <? if ($column->increment) continue ?>
      <?= $colName ?><br/>
      <? if ($column->isBool()) : ?>
        <?= $$formName->checkbox($colName) ?>
      <? elseif ($column->isText()) : ?>
        <?= $$formName->textarea($colName) ?>
      <? elseif ($column->isDatetime()) : ?>
        <?= $$formName->datetime($colName, null, true) ?>
      <? elseif ($column->isDate()) : ?>
        <?= $$formName->date($colName) ?>
      <? else : ?>
        <?= $$formName->text($colName) ?>
      <? endif ?>
      <br/>
    <? endforeach ?>
    <?= $$formName->submit("add") ?>
  <?= $$formName->end() ?>
</div>

<script type="text/javascript">

<? if ($models) : ?>
var rowCount = <?= count($models) ?>;
var mdlName  = "<?= $models[0]->getName() ?>";
<? else : ?>
var rowCount = 0;
var mdlName  = "";
<? endif ?>

var currentElem = null;

function turnover(checked)
{
  for (var i = 0; i < rowCount; i++) {
    var cb = Sabel.get("delkey" + i);
    cb.checked = checked;
  }
}

function edit(evt, key)
{
  if (evt.target.nodeName == "INPUT") return;

  new Sabel.Ajax().Request("/admin/table/prepareEdit/" + mdlName,
                           { params: "key=" + key,
                             onComplete: function(res) {
                               Sabel.get("model_form").innerHTML = res.responseText;
                             }
                           });
}

</script>
