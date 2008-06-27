<div class="sbl_pager">
<?php if ($paginate->count > $paginate->limit) : ?>
  <a class="prev" href="<?php echo uri($paginate->uri) ?>?<?php echo $paginate->getUriQuery($paginate->viewer->getFirst()) ?>">&lt;&lt;</a>
  
  <?php foreach ($paginate->viewer as $v) : ?>
    <?php if ($v->isCurrent()) : ?>
      <span><?php echo $v->getCurrent() ?></span>
    <?php else : ?>
      <?php echo a($paginate->uri, $v->getCurrent(), $paginate->getUriQuery($v->getCurrent())) ?>
    <?php endif ?>
  <?php endforeach ?>
  
  <a class="next" href="<?php echo uri($paginate->uri) ?>?<?php echo $paginate->getUriQuery($paginate->viewer->getLast()) ?>">&gt;&gt;</a>
<?php endif ?>
</div>
