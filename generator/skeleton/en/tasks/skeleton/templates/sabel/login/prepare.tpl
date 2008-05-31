<h2>Login</h2>

<partial name="error" />

<form action="<#= uri("a: doLogin") #>" method="post">
  <dl>
    <dt><?php echo $emailColumn ?></dt>
    <dd><input type="text" name="<?php echo $emailColumn ?>" value="<#= $<?php echo $emailColumn ?> #>" /></dd>
    <dt>password</dt>
    <dd><input type="password" name="password" value="<#= $password #>" /></dd>
  </dl>
  
  <div>
    <input type="submit" value="Login" />
  </div>
</form>
