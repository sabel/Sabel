<h2>lists</h2>

<div>
<# foreach ($<? echo $name ?>s as $bbs) : #>

  <div>
  <# foreach ($<? echo $name ?> as $column) : #>
    <#= $column->name #>: &nbsp;
    <#hn $column->value #>
    <br />
  <# endforeach #>
  </div>
  
  <#= a('action:create', _('create')) #> 
  <#= a("a:show,   id:{$<? echo $name ?>->id->value}", _('show')) #>
  <#= a("a:edit,   id:{$<? echo $name ?>->id->value}", _('edit')) #>
  <#= a("a:delete, id:{$<? echo $name ?>->id->value}", _('delete')) #>
  
  <hr />
<# endforeach #>
</div>