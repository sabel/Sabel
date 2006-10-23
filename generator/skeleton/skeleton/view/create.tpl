<h2>create <? echo $name ?></h2>

<#= $this->partial('error') #>

<# $form = new Sabel_Template_Form($<? echo $name ?>, (isset($errors)) ? $errors : null) #>
<# $form->hidden(array('id')) #>
<#= $form->startTag(uri(array('action' => 'create')), 'POST') #>
<# foreach ($form as $f) : ?>
  <#= $f->write("{$f->name()}<br />", "<br /><br />") #>
<# endforeach #>
<#= $form->submitTag('edit') #>
<#= $form->endTag() #>

<#= a("a:lists", _('list')) #>