<html>
<head>
  <? js_include('prototype.js') ?>
<script>

function click(ev)
{
  $('body_' + ev).style.display = 'none'
  $('form_' + ev).style.display = 'block'
}

function toggle(id1, id2)
{
	if (document.getElementById(id1).style.display == 'none') {
		document.getElementById(id1).style.display = 'block'
		document.getElementById(id2).style.display = 'none'
	} else {
		document.getElementById(id1).style.display = 'none'
		document.getElementById(id2).style.display = 'block'
	}
	return false;
}

</script>

</head>
<body>

�֥���ɽ��
<hr/>

<table border="1">
  <tr>
    <td>��̾</td>
    <td>��ʸ</td>
    <td>�������</td>
    <td>������</td>
  </tr>

  <!-- <? /* �����֤���ʬ���� */ ?> -->
  <? foreach ($this->blogs as $blog): ?>
  <tr>
    <td>
      <? $this->eprintWithDefault($blog->getSubject(), '̵��') ?>
    </td>

    <td>
      <? $bid = $blog->getID() ?>
      <div id="body_<? p($bid) ?>">
	<? $this->eprintWithDefault($blog->getBody(), '����ä�') ?>
	<a href="#" onclick="toggle('body_<? p($bid) ?>', 'form_<? p($bid) ?>')">[edit]</a>
      </div>
      <div id="form_<? p($bid) ?>" style="display: none;">
	<form action="/Show/blog/update/<? p($bid) ?>" method="post">
	  <input type="text" name="body" value="<? $this->eprint($blog->getBody()) ?>">
	  <input type="submit" value="����"/>
	  <a href="#" onclick="toggle('body_<? p($bid) ?>', 'form_<? p($bid) ?>')">[cancel]</a>
	</form>
      </div>
    </td>

    <td>
      <? linkTo( array(IMG    => 'images/abc.gif', 
                       METHOD => 'delete',
                       PARAM  => $blog->getID() )) ?>
    </td>
    <td>
      <? linkTo( array(LNAME  => '���', 
                       METHOD => 'delete',
                       PARAM  => $blog->getID() )) ?>
    </td>
    <td>
      <? linkTo( array(LNAME  => 'show',
                       METHOD => 'showFromID',
                       PARAM  => $blog->getID())) ?>
    </td>
  </tr>
  <? endforeach ?>
  <!-- <? /* ��λ */ ?> -->

</table>

<hr/>

<form action="/Show/blog/postWrite" method="post">
  subject<input type="text" name="subject" /><br/>
  body<input type="text" name="body"/> <input type="submit"/>
</form>

<? linkTo(array(IMG=>'images/abc.gif',
                ALT=>'description',
                METHOD=>'write')) ?>

</body>
</html>
