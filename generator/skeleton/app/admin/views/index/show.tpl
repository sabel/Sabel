<? if ($tables) : ?>
  <? foreach ($tables as $table) : ?>
    <?= a("c: table, a: view, param: {$table}", $table) ?><br/>
  <? endforeach ?>
<? else : ?>
  No tables found in database.<br/>
<? endif ?>

<br/>
