<h2>Login</h2>

{php}echo $this->get_template_vars("renderer")->partial("error"){/php}

<form action="{"a: doLogin"|uri}" method="post">
  <dl>
    <dt><?php echo $emailColumn ?></dt>
    <dd><input type="text" name="<?php echo $emailColumn ?>" value="{$<?php echo $emailColumn ?>|h}" /></dd>
    <dt>password</dt>
    <dd><input type="password" name="password" value="{$password|h}" /></dd>
  </dl>
  
  <div>
    <input type="submit" value="Login" />
  </div>
</form>
