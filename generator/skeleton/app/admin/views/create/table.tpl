<form action="<?= uri("a: addConstraints") ?>" method="POST">
  <div id="create_table" style="width: 640px;">
    TABLE NAME<br/>
    <input type="text" name="table_name" style="width: 250px;" /><br/>
    <br/>
    <div class="name">COLUMN NAME</div>
    <div class="type">DATA TYPE</div>
    <div class="length">LENGTH</div>
    <div class="pkey">PRIMARY KEY</div>
    <div class="not_null">NOT NULL</div>
    <div class="default">DEFAULT</div>
    <div id="column_def_area"></div>
    <input type="button" value="add column" onclick="addColumn()" />
  </div>
  <input type="submit" value="next" />
</form>


<script type="text/javascript">

var html = '<div class="name">\n'
         + '  <input type="text" name="column_name[]" style="width: 160px;"/>\n'
         + '</div>\n'
         + '<div class="type">\n'
         + '  <select id="seltype%NUM%" name="column_type[]" '
         + 'style="width: 100px;" onchange="changeType(%NUM%)">\n'
         + '    <option>INT</option>\n'
         + '    <option>SMALLINT</option>\n'
         + '    <option>BIGINT</option>\n'
         + '    <option>STRING</option>\n'
         + '    <option>TEXT</option>\n'
         + '    <option>BOOL</option>\n'
         + '    <option>FLOAT</option>\n'
         + '    <option>DOUBLE</option>\n'
         + '    <option>DATETIME</option>\n'
         + '    <option>DATE</option>\n'
         + '  </select>\n'
         + '</div>\n'
         + '<div class="length">\n'
         + '  <span id="length_%NUM%">&nbsp;</span>\n'
         + '</div>\n'
         + '<div class="pkey">\n'
         + '  <input type="checkbox" name="primary[]" value="1" />\n'
         + '</div>\n'
         + '<div class="not_null">\n'
         + '  <input type="checkbox" name="notnull[]" value="1" />\n'
         + '</div>\n'
         + '<div class="default">\n'
         + '  <input type="text" name="default[]" style="width: 100px;" />\n'
         + '</div>\n';

function addColumn()
{
  var area = Sabel.get('column_def_area');
  area.innerHTML = area.innerHTML + html;
}

function init()
{
  var htmls = new Array();
  for (var i = 1; i <= 8; i++) {
    htmls.push(html.replace(/%NUM%/g, i));
  }

  Sabel.get('column_def_area').innerHTML = htmls.join('\n');
}

function changeType(num)
{
  var type = Sabel.get('seltype' + num).value;
  if (type == 'STRING') {
    var elem = Sabel.get('length_' + num);
    elem.innerHTML = '<input type="text" name="length[]" style="width: 40px; text-align: right;" />';
    elem.firstChild.focus();
  } else {
    Sabel.get('length_' + num).innerHTML = '&nbsp;';
  }
}

init();

</script>
