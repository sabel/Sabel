<? if (isset($errors)) : ?>
<div id="sbl_errmsg" class="errmsg">
  <ul>
    <? foreach ($errors as $error) : ?>
      <li><?= $error ?></li>
    <? endforeach ?>
  </ul>
<? else : ?>
<div id="sbl_errmsg" class="errmsg" style="display: none;">
<? endif ?>
</div>
