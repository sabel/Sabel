<div style="border: 1px dotted;">
  メニュー<br />
  <a href="/blog/common/show">表示</a>
  <? for ($i = 0; $i <= 80; $i += 5) : ?>
    <a href="/<? echo $i ?>"><? echo $i ?></a>
  <? endfor; ?>
</div>
