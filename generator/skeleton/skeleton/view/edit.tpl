<h2>edit <? $name ?> No.<#= $<? echo $name ?>->id #></h2>

<#= $this->partial('error') #>

<# $form = new Sabel_Template_Form($<? echo $name ?>->schema(), (isset($errors)) ? $errors : null) #>
<# $form->hidden(array('id')) #>
<#= $form->startTag(uri(array('action' => 'edit', 'id' => $<? echo $name ?>->id)), 'POST') #>
<# foreach ($form as $f) : ?>
  <#= $f->write("{$f->name()}<br />", "<br /><br />") #>
<# endforeach #>
<#= $form->submitTag('edit') #>
<#= $form->endTag() #>

<#= a("a: edit, id: {$<? echo $name ?>->id}", _('edit')) #>&nbsp;
<#= a('action: create', _('create')) #>&nbsp;
<#= a('action: lists', _('list')) #>