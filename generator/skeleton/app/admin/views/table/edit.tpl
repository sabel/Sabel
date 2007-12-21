<h2>edit data</h2>

<? $columns = $itemForm->getModel()->getSchema()->getColumns() ?>

<? if ($itemForm->hasError()) : ?>
  <?= $this->partial("error", null, array("errors" => $itemForm->getErrors())) ?>
<? endif ?>

<?= $itemForm->start("a: doEdit") ?>
  
  <? foreach ($columns as $name => $column) : ?>
    <p class="name">- <?= $name ?> -</p>
    <? if ($column->isText()) : ?>
      <?= $itemForm->textarea($name) ?>
    <? else : ?>
      <?= $itemForm->text($name, "w300") ?>
    <? endif ?>
  <? endforeach ?>
  
  <br/>
  
  <?= $itemForm->submit("edit") ?>
<?= $itemForm->end() ?>
