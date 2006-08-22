<h2><? echo $name ?> lists</h2>

<# foreach ($<? echo $name ?>s as $<? echo $name ?>) : #>
  <# foreach ($<? echo $name ?>->toArray() as $name => $val) : #>
    <# echo $name #>:&nbsp;
    <# echo nl2br($<? echo $name ?>->$name) #>
    <br />
  <# endforeach #>
  <a href="/index/<? echo $name ?>/show/<# echo $<? echo $name ?>->id #>">show</a>
  <a href="/index/<? echo $name ?>/edit/<# echo $<? echo $name ?>->id #>">edit</a>
  <a href="/index/<? echo $name ?>/delete/<# echo $<? echo $name ?>->id #>">delete</a>
  <hr />
<# endforeach #>

<a href="/index/<? echo $name ?>/create">new</a>