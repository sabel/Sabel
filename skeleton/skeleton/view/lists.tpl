<h2>lists</h2>

<div>
<# foreach ($<? echo $name ?>s as $<? echo $name ?>) : #>
  <div>
  <# foreach ($<? echo $name ?> as $column) : #>
    <#= $column->name #>: &nbsp;
    <#hn $column->value #>
    <br />
  <# endforeach #>
  </div>
  
  <a href="<#= urlFor('default', 'show',   $<? echo $name ?>) ?>"><#= _('show') #></a>
  <a href="<#= urlFor('default', 'edit',   $<? echo $name ?>) ?>"><#= _('edit') #></a>
  <a href="<#= urlFor('default', 'delete', $<? echo $name ?>) ?>"><#= _('delete') #></a>
  
  <hr />
<# endforeach #>
</div>

<a href="/index/<? echo $name ?>/create"><#= _('create') #></a>