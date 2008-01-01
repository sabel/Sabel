<? if ($paginate->count > $paginate->limit) : ?>
  <?= a($pagerUri, "&lt;&lt;", "?page=" . $paginate->viewer->getFirst()) ?>
  <? foreach ($paginate->viewer as $v) : ?>
    <? if ($v->isCurrent()) : ?>
      <?= $v->getCurrent() ?>
    <? else : ?>
      <?= a($pagerUri, $v->getCurrent(), "?page=" . $v->getCurrent()) ?>
    <? endif ?>
  <? endforeach ?>
  <?= a($pagerUri, "&gt;&gt;", "?page=" . $paginate->viewer->getLast()) ?>
<? endif ?>
