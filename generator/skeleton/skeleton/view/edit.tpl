<h2>edit bbs No.<#= $<? echo $name ?>->id #></h2>

<#= $this->partial('error') #>

<div>
  <form method="post" action="<#= uri(array('action'=>'edit', 'id'=>$<? echo $name ?>->id)) ?>">
    <# $this->assign('model', $<? echo $name ?>) #>
    <#= $this->partial('form') #>
    <input type="submit" value="edit" />
  </form>
</div>

<#= a("a: edit, id: {$<? echo $name ?>->id->value}", _('edit')) #> 
<#= a('action: create', _('create')) #> 
<#= a('action: lists', _('list')) #>