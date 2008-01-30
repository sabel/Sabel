<? if ($paginate->count > $paginate->limit) : ?>
  <?= a($paginate->uri, "&lt;&lt;", $paginate->getQueryString($paginate->viewer->getFirst())) ?>
  <? foreach ($paginate->viewer as $v) : ?>
    <? if ($v->isCurrent()) : ?>
      <?= $v->getCurrent() ?>
    <? else : ?>
      <?= a($paginate->uri, $v->getCurrent(), $paginate->getQueryString($v->getCurrent())) ?>
    <? endif ?>
  <? endforeach ?>
  <?= a($paginate->uri, "&gt;&gt;", $paginate->getQueryString($paginate->viewer->getLast())) ?>
<? endif ?>
