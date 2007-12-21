<? if (isset($errors)) : ?>
  <div class="errmsg">
    <ul>
      <? foreach ($errors as $error) : ?>
        <li><?= $error ?></li>
      <? endforeach ?>
    </ul>
  </div>
<? endif ?>

<? if (isset($errmsg) && $errmsg !== "") : ?>
  <div class="errmsg">
    <ul>
      <li><?= $errmsg ?></li>
    </ul>
  </div>
<? endif ?>
