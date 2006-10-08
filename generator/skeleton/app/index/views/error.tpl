<? if (isset($errors) && is_object($errors)) :?>
<div id="error" style="padding: 5px; margin: 5px; border: 1px dotted; background-color: #F99;">
  need fix listing below problems.
  <ul>
  <? foreach ($errors->getErrors() as $error) : ?>
    <li><?= $error->getMessage() ?></li>
  <? endforeach ?>
  </ul>
</div>
<? endif ?>