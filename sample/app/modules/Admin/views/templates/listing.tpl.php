<html>
    <head>
      <title><? $this->eprint($this->title); ?></title>
    </head>
<body>

<p>test!</p>

<? /* 繰り返し部分 危険さわるな */ ?>
<? foreach ($this->users as $k => $user): ?>
  <p><? $this->eprint($user) /* ユーザ名出力部分 */ ?></p>
<? endforeach; ?>

<? linkTo("リンク", 'Admin', 'ListUsers', 'listing', $this->value) ?>

</body>
</html>