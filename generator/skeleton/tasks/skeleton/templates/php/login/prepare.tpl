<h2>Login</h2>

<#php echo $this->partial("error") #>

<form action="<#php echo uri("a: doLogin") #>" method="post">
  <dl>
    <dt><?php echo $emailColumn ?></dt>
    <#php if (isset($<?php echo $emailColumn ?>)) : #>
    <dd><input type="text" name="<?php echo $emailColumn ?>" value="<#php echo h($<?php echo $emailColumn ?>) #>" /></dd>
    <#php else : #>
    <dd><input type="text" name="<?php echo $emailColumn ?>" value="" /></dd>
    <#php endif #>
    <dt>password</dt>
    <#php if (isset($password)) : #>
    <dd><input type="password" name="password" value="<#php echo h($password) #>" /></dd>
    <#php else : #>
    <dd><input type="password" name="password" value="" /></dd>
    <#php endif #>
  </dl>
  
  <div>
    <input type="submit" value="Login" />
  </div>
</form>
