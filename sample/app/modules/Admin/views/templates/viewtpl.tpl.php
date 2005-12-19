<html>
    <head>
      <title><? $this->eprint($this->title); ?></title>
    </head>
<body>

<p>test!</p>

<p>value = <? $this->eprint($this->testval) ?> </p>

<form action="/Admin/ListUsers/posted" method="POST">
  <input type="text" name="test" value="123"/>
  <input type="submit" value="Á÷¿®"/>
</form>

<? linkTo("link", 'Admin', 'ListUsers', 'viewtpl', $this->value) ?>

</body>
</html>