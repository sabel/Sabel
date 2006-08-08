<? foreach ($articles as $article) : ?>
  <h2><? echo $article->title ?></h2>
  <p><? echo $article->body ?></p>
  <p>著者: <? echo $article->author->name ?> </p>

<!--
<pre>
<? var_dump($article) ?>
</pre>
-->

<? endforeach; ?>
