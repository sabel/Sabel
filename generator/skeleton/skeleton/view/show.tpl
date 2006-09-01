<h1>show <? echo $name ?></h1>

<# foreach ($<? echo $name ?> as $column) : #>
  <# echo $column->name #>:&nbsp;
  <# echo nl2br($column->value) #>
  <br />
<# endforeach #>

<a href="<#= urlFor('default', 'edit',   $<? echo $name ?>) #>"><#= _('show') #></a>
<a href="<#= urlFor('default', 'edit',   $<? echo $name ?>) #>"><#= _('edit') #></a>
<a href="<#= urlFor('default', 'delete', $<? echo $name ?>) #>"><#= _('delete') #></a>