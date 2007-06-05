<? if (isset($errors)) : ?>
  <div style="border: 4px solid red; padding: 10px; background-color: #FFF5F5;">
  <? foreach ($errors as $error) : ?>
    <?= $error ?><br/>
  <? endforeach ?>
  </div>
  <br/>
<? endif ?>
