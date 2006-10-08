<? foreach ($model as $column) : ?>
  <?= $column->name ?>:
  
  <? if (isset($errors) && is_object($errors) && $errors->errored($column->name)) : ?>
  <? $error = $errors->get($column->name) ?>
  <input type="text" name="<?= $column->name ?>" value="<?= $error->getValue() ?>"
         style="background-color: #FCC;">
  <? else : ?>
  <input type="text" name="<?= $column->name ?>" value="<?hn $column->value ?>" />
  <? endif ?>
  <br />
<? endforeach ?>