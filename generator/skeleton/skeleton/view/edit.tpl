<h2>edit <? $name ?> No.<#= $<? echo $name ?>->id #></h2>

<#= $this->partial('error') #>

<div>
  <form method="post" action="<#= uri(array('action'=>'edit', 'id'=>$<? echo $name ?>->id)) ?>">
    <# $this->assign('schema', $<? echo $name ?>->schema()) #>
    <#= $this->partial('form') #>
    <input type="submit" value="edit" />
  </form>
</div>

<#= a("a: edit, id: {$<? echo $name ?>->id}", _('edit')) #> 
<#= a('action: create', _('create')) #> 
<#= a('action: lists', _('list')) #>