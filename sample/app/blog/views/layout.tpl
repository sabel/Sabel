<html>
  <head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    <title><? $id ?></title>
  </head>
<body>

  <div style="border: 2px solid;">
    レイアウト Header.
  </div>

  <br />

  <? $this->load_template('menu') ?> <br />
  <? echo $this->content_for_layout ?> <br />

  <div style="border: 2px solid;">
    レイアウトFooter.
  </div>

</body>
</html>
