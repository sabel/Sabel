<h2>create <? echo $name ?></h2>

<div>
<form method="post" action="<#= urlFor('default', 'create') #>">
<# foreach ($<? echo $name ?> as $column) : #>
  <#= $column->name #>:
  <input type="text" name="<#= $column->name #>" value="" />
  <br />
<# endforeach #>
<input type="submit" value="create" />
</form>
</div>

<a href="<#= urlFor('default', 'lists') #>"><#= _('list') #></a>