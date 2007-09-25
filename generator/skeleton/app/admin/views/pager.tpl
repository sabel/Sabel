<? if ($paginate->count > $paginate->limit) : ?>
  <? $viewer = new Sabel_View_PageViewer($paginate->pager) ?>
  <?= a("", "&lt;&lt;", "?page=" . $viewer->getFirst()) ?>
  <? foreach ($viewer as $v) : ?>
    <? if ($v->isCurrent()) : ?>
      <?= $v->getCurrent() ?>
    <? else : ?>
      <?= a("", $v->getCurrent(), "?page=" . $v->getCurrent()) ?>
    <? endif ?>
  <? endforeach ?>
  <?= a("", "&gt;&gt;", "?page=" . $viewer->getLast()) ?>
<? endif ?>
