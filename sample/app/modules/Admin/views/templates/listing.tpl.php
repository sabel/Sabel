<html>
    <head>
      <title><? $this->eprint($this->title); ?></title>
    </head>
<body>

<p>test!</p>

<? /* �����֤���ʬ �������� */ ?>
<? foreach ($this->users as $k => $user): ?>
  <p><? $this->eprint($user) /* �桼��̾������ʬ */ ?></p>
<? endforeach; ?>

<? linkTo("���", 'Admin', 'ListUsers', 'listing', $this->value) ?>

</body>
</html>