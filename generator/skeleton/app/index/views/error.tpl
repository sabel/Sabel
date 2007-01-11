<? if (isset($errors) && is_object($errors)) : ?>
  <div style="border: 4px solid red; padding: 10px; background-color: #F8E9E9; font-size: 0.9em;">
  <? foreach ($errors->toArray() as $errored => $msg) : ?>
    <?= $errored ?><?= $msg ?><br/>
  <? endforeach ?>
  </div>
<? endif ?>
