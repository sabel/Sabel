<div style="border: 1px dotted;">
  メニューです！<br />
  メニューからでも、Assignされた値は使えるよ！ 記事番号: <? echo $id ?> とかね<br />
  <a href="/blog/common/show">表示</a>

  <? for ($i = 0; $i <= 100; $i += 5) : ?>
    <a href="/<? echo $i ?>"><? echo $i ?></a>
  <? endfor; ?>
</div>
