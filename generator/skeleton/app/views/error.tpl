<div id="sbl_errmsg" class="errmsg">
<? if (isset($errors)) : ?>
  <ul>
    <? foreach ($errors as $error) : ?>
      <li><?= $error ?></li>
    <? endforeach ?>
  </ul>
<? endif ?>
</div>
