<div class="sbl_pager">
<? if ($paginate->count > $paginate->limit) : ?>
  <?= a($paginate->uri, "&lt;&lt;", $paginate->getUriQuery($paginate->viewer->getFirst())) ?>
  <? foreach ($paginate->viewer as $v) : ?>
    <? if ($v->isCurrent()) : ?>
      <?= $v->getCurrent() ?>
    <? else : ?>
      <?= a($paginate->uri, $v->getCurrent(), $paginate->getUriQuery($v->getCurrent())) ?>
    <? endif ?>
  <? endforeach ?>
  <?= a($paginate->uri, "&gt;&gt;", $paginate->getUriQuery($paginate->viewer->getLast())) ?>
<? endif ?>
</div>
