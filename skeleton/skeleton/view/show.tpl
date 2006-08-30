<h1>show <? echo $name ?></h1>

<# foreach ($<? echo $name ?> as $column) : #>
  <# echo $column->name #>:&nbsp;
  <# echo nl2br($column->value) #>
  <br />
<# endforeach #>

<a href="/index/<? echo $name ?>/edit/<# echo $<? echo $name ?>->id #>">edit</a>
<a href="/index/<? echo $name ?>/create">new</a>
<a href="/index/<? echo $name ?>/lists">lists</a>