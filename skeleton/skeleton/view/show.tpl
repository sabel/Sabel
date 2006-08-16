<h1>show <? echo $name ?></h1>

<# foreach ($blog->toArray() as $name => $val) : #>
  <# echo $name #>:&nbsp;
  <# echo nl2br($<? echo $name ?>->$name) #>
  <br />
<# endforeach #>

<a href="/index/<? echo $name ?>/edit/<# echo $<? echo $name ?>->id #>">edit</a>
<a href="/index/<? echo $name ?>/create">new</a>
<a href="/index/<? echo $name ?>/lists">lists</a>