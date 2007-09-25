<h1>
  フレームワークで例外が発生しました
</h1>

下記のエラーを確認してください。<br/>
<br/>

<div style="border: solid 2px #f00; mergin: 10px; padding: 10px;">
  <pre style="font-size: 1.2em;"><b><?= $exception->getMessage() ?></b></pre>
</div>

<br/>

<pre>
<?= print_r($exception->getTraceAsString()) ?>
</pre>
