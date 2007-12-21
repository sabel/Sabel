<h2>tables</h2>

<? if ($tables) : ?>
  <ul>
  <? foreach($tables as $table) : ?>
    <li>
      <a href="<?= uri("c: table, a: show, param: {$table}") ?>">
        <?= $table ?>
      </a>
    </li>
  <? endforeach ?>
  </ul>
<? else : ?>
  empty set.
<? endif ?>
