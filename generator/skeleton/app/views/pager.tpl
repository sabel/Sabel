<div class="sbl_pager">
<?php if ($paginate->count > $paginate->limit) : ?>
  <?php echo a($paginate->uri, "&lt;&lt;", $paginate->getUriQuery($paginate->viewer->getFirst())) ?>
  <?php foreach ($paginate->viewer as $v) : ?>
    <?php if ($v->isCurrent()) : ?>
      <?= $v->getCurrent() ?>
    <?php else : ?>
      <?= a($paginate->uri, $v->getCurrent(), $paginate->getUriQuery($v->getCurrent())) ?>
    <?php endif ?>
  <?php endforeach ?>
  <?php echo a($paginate->uri, "&gt;&gt;", $paginate->getUriQuery($paginate->viewer->getLast())) ?>
<?php endif ?>
</div>
