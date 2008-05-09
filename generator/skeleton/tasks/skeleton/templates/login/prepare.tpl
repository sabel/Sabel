<h2>Login</h2>

<#= $this->partial("error") #>

<form action="<#= uri("a: doLogin") #>" method="post">
  <dl>
    <dt><?php echo $emailColumn ?></dt>
    <dd><input type="text" name="<?php echo $emailColumn ?>" value="<#= $<?php echo $emailColumn ?>|h #>" /></dd>
    <dt>password</dt>
    <dd><input type="password" name="password" value="<#= $password|h #>" /></dd>
  </dl>
  
  <div>
    <input type="submit" value="Login" />
  </div>
</form>
