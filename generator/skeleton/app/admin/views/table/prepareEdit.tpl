<?= $$formName->start("a: edit, param: {$$formName->getModel()->getName()}") ?>
  <? foreach ($schema->getColumns() as $colName => $column) : ?>
    <? if ($column->increment || $column->primary) continue ?>
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
  <?= $$formName->submit("edit") ?>
<?= $$formName->end() ?>
