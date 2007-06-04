<h1>
<? if ($exceptionType === "database") : ?>
  データベースで
<? else : ?>
  フレームワークで
<? endif ?>
例外が発生しました</h1>

<? if ($exceptionType === "database") : ?>
  config/connection.phpを確認してください。<br/>
<? endif ?>

<hr/>

下記の詳細なエラーを確認してください。
<div style="border: solid 1px #fcc; mergin: 10px; padding: 10px;">
<pre style="font-size: 1.2em;"><?= $exception->getMessage() ?></pre>
</div>

<hr/>

<pre>
<?= print_r($exception->getTraceAsString()) ?>
</pre>
