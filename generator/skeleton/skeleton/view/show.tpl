<h1>show <? echo $name ?></h1>

<# foreach ($<? echo $name ?> as $column) : ?>
  <#= $column->name ?>:&nbsp;
  <#= nl2br($column->value) ?>
  <br />
<# endforeach ?>

<#= a('action:create', _('create')) ?> 
<#= a('action:lists', _('list')) ?> 
<#= a("a:show,   id:{$<? echo $name ?>->id->value}", _('show')) ?>
<#= a("a:edit,   id:{$<? echo $name ?>->id->value}", _('edit')) ?>
<#= a("a:delete, id:{$<? echo $name ?>->id->value}", _('delete')) ?>