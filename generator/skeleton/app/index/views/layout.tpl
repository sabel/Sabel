<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title><?= $pageTitle ?></title>
  <meta http-equiv="Content-Language" content="English" />
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="author" content="" />
  <meta name="description" content="" />
  <meta name="keywords" content="" />	
  <?= css("default") ?>
</head>
<body>

<div id="header">
  <h1>Sabel</h1>
  <h2>Welcome to Sabel have fun!</h2>
</div>

<div class="content">
  <div id="contents">
    <?= $contentForLayout ?>
  </div>
  
  <div id="footer">
    <div class="right">&copy; Copyright 2007, </div>
  </div>
</div>

</body>
</html>
