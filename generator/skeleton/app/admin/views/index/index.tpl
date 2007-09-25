<h2>- Connection Settings -</h2>

<? foreach ($configs as $name => $params) : ?>
  <h3>
    <?= a("a: show", $name, "?db={$name}") ?>
  <? if ($params["state"] === true) : ?>
    <font color="blue">(enable)</font>
  <? else : ?>
    <font color="red">(<?= $params["state"] ?>)</font>
  <? endif ?>
  <? unset($params["state"]) ?>
  </h3>
  <ul>
  <? foreach ($params as $key => $value) : ?>
    <li><?= $key ?>: <?= $value ?></li>
  <? endforeach ?>
  </ul>
<? endforeach ?>

<br/>
<input id="open_button" type="button" value="open file" onclick="config.openFile()" ?>

<div id="config_area" style="display: none;">
  <span id="warn"></span>
  <form action="<?= uri("a: saveConfigFile") ?>" method="POST" name="fileform">
    <textarea id="contarea" name="content" style="height: 400px; width: 600px;"></textarea>
    <br/>
    <input type="button" value="save"  onclick="document.fileform.submit()" />
    <input type="button" value="close" onclick="config.closeFile()" />
  </form>
</div>

<div id="black_layer" style="display: none;"></div>

<script type="text/javascript">

function Config() {
  this.initialize.apply(this, arguments);
}

Config.prototype = {
  initialize: function(options)
  {

  },

  openFile: function()
  {
    new Sabel.Ajax().Request('/admin/index/openConfigFile',
                             { onComplete: this.showFile });
  },

  closeFile: function()
  {
    Sabel.get('config_area').style.display = 'none';
    var bl = Sabel.get('black_layer');
    bl.style.zIndex  = -1;
    bl.style.display = 'none';
  },

  showFile: function(res)
  {
    var bl = Sabel.get('black_layer');
    bl.style.zIndex = 5000;
    bl.style.display = 'block';

    var fa = Sabel.get('config_area');
    eval('var results = ' + decodeURIComponent(res.responseText));

    if (!results.writeable) {
      var text = '<font color="red">connection.php is not writeable.</font>';
      Sabel.get('warn').innerHTML = text;
    }

    Sabel.get('contarea').value = results.content;

    fa.style.left    = Math.round((document.width - 550) / 2) + 'px';
    fa.style.display = 'block';
    fa.style.zIndex  = 10000;
  },
}

var config = new Config();

</script>
