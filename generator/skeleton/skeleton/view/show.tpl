<h1>show <? echo $name ?></h1>

<# foreach ($<? echo $name ?>->schema() as $column) : #>
  <#= $column->name #>:&nbsp;
  <#= nl2br($column->value) #>
  <br />
<# endforeach #>

<#= a('action:create', _('create')) #> 
<#= a('action:lists', _('list')) #> 
<#= a("a:show,   id:{$<? echo $name ?>->id}", _('show')) #>&nbsp;
<#= a("a:edit,   id:{$<? echo $name ?>->id}", _('edit')) #>&nbsp;
<#= a("a:delete, id:{$<? echo $name ?>->id}", _('delete')) #>