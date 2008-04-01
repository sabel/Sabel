<? if (!empty($errors)) : ?>
<div id="sbl_errmsg" class="sbl_error">
  <ul>
    <? foreach ($errors as $error) : ?>
      <li><?= $error ?></li>
    <? endforeach ?>
  </ul>
<? else : ?>
<div id="sbl_errmsg" class="sbl_error" style="display: none;">
<? endif ?>
</div>
