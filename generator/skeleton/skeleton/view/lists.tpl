<h2>lists</h2>

<div>
<# foreach ($<? echo $name ?>s as $<? echo $name ?>) : #>

  <div>
  <# foreach ($<? echo $name ?>->schema() as $column) : #>
    <#= $column->name #>: &nbsp;
    <#hn $column->value #>
    <br />
  <# endforeach #>
  </div>
  
  <#= a('action:create', _('create')) #> 
  <#= a("a:show,   id:{$<? echo $name ?>->id}", _('show')) #>
  <#= a("a:edit,   id:{$<? echo $name ?>->id}", _('edit')) #>
  <#= a("a:delete, id:{$<? echo $name ?>->id}", _('delete')) #>
  
  <hr />
<# endforeach #>
</div>