<h2>lists</h2>

<# foreach ($<? echo $name ?>s as $<? echo $name ?>) : #>

  <# $form = new Sabel_Template_Form($<? echo $name ?>->schema(), (isset($errors)) ? $errors : null) #>
  <# $form->hidden(array('id')) #>
  <# $form->hiddenPattern('.*(_id)') #>
  <# foreach ($form as $f) : #>
    <# if (!$f->isHidden()) : #>
    <#= $f->name() #> <br />
    <#= $f->value() #> <br /><br />
    <# endif #>
  <# endforeach #>
  
  <#= a('action:create', _('create')) #>&nbsp;
  <#= a("a:show,   id:{$<? echo $name ?>->id}", _('show')) #>&nbsp;
  <#= a("a:edit,   id:{$<? echo $name ?>->id}", _('edit')) #>&nbsp;
  <#= a("a:delete, id:{$<? echo $name ?>->id}", _('delete')) #>
  
  <hr />
<# endforeach #>