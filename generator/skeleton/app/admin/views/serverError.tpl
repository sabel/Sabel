<h2>サーバーでエラーが発生しました</h2>

<p>
  ページを表示できません(500 Internal Server Error)
</p>

<? if (isset($exception_message)) : ?>
<div style="border: solid 2px #f00; mergin: 10px; padding: 10px;">
  <?= $exception_message ?>
</div>
<? endif ?>
