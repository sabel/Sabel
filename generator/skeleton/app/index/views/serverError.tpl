<h1>
<? if ($exceptionType === "database") : ?>
  データベースで
<? else : ?>
  フレームワークで
<? endif ?>
例外が発生しました
</h1>

下記のエラーを確認してください。<br/>
<br/>

<div style="border: solid 3px #f88; mergin: 10px; padding: 10px;">
  <pre style="font-size: 1.3em;"><?= $exception->getMessage() ?></pre>
</div>

<br/>

<pre>
<?= print_r($exception->getTraceAsString()) ?>
</pre>
