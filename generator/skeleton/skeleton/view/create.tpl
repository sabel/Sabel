<h2>create <? echo $name ?></h2>

<#= $this->partial('error') #>

<div>
  <form method="post" action="<#= uri(array('action'=>'create')) ?>">
    <# $this->assign('model', $<? echo $name ?>) #>
    <#= $this->partial('form') #>
    <input type="submit" value="create" />
  </form>
</div>

<#= a("a:lists", _('list')) #>