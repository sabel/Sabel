<? foreach ($articles as $article) : ?>
<div style="border: 1px dotted; margin: 10px;">
  <h4><? echo $article->title ?></h4>
  <p><? echo $article->body ?></p>
  <p>著者: <? echo $article->author->name ?>(<? echo $article->author->age ?>) </p>
</div>
<? endforeach; ?>
