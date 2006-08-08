<html>
<head>
  <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
  <Title>インデックス - <? $this->p->title ?></title>
</head>

<body>
  <h1><? $this->p->test; ?></h1>

　<p>ループ</p>
  <? foreach ($this->ar as $item) : ?>
    <? print $item ?>,
  <? endforeach; ?>
</body>
</html>
