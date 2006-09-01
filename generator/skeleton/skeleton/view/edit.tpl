<h2>edit <? echo $name ?> No.<#= $<? echo $name ?>->id->value #></h2>

<div>
<form method="post" action="<#= urlFor('default', 'edit', $<? echo $name ?>) #>">
<# foreach ($<? echo $name ?> as $column) : #>
  <#= $column->name #>:
  <input type="text" name="<#= $column->name #>" value="<#hn $column->value #>" />
  <br />
<# endforeach #>
<input type="submit" value="edit" />
</form>
</div>

<a href="<#= urlFor('default', 'edit', $<? echo $name ?>) #>"><#= _('edit') #></a>
<a href="<#= urlFor('default', 'create') #>"><#= _('create') #></a>
<a href="<#= urlFor('default', 'lists') #>"><#= _('list') #></a>