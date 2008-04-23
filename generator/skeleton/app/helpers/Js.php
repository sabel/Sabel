<?php

class Helpers_Js
{
  public static function effectUpdater($sourceId, $replaceId, $effect = "Slide")
  {
    $include = <<<INC
<script type="text/javascript" src="%s" charset="UTF-8"></script>
INC;

    $script = <<<JS
<script type="text/javascript">
new Sabel.Event(window, "load", function() {
  var updater = new Sabel.PHP.EffectUpdater("%s", "%s");

  Sabel.get("%s").observe("click", function(evt) {
    updater.fire(this.href);
    Sabel.Event.preventDefault(evt);
  });
});
</script>
JS;

    $buf   = array();
    $buf[] = sprintf($include, linkTo("js/helpers/EffectUpdater.js"));

    $buf[] = sprintf($script, $replaceId, $effect, $sourceId);

    return join($buf, "\n");
  }

  public static function ajaxPager($replaceId, $pagerClass = "sbl_pager")
  {
    $include = <<<INC
<script type="text/javascript" src="%s" charset="UTF-8"></script>
INC;

    $buf   = array();
    $buf[] = sprintf($include, linkTo("js/helpers/EffectUpdater.js"));
    $buf[] = sprintf($include, linkTo("js/helpers/AjaxUpdater.js"));

    $buf[] = "\n";
    $buf[] = '<script type="text/javascript">';
    $buf[] = sprintf('new Sabel.Event(window, "load", function() { new Sabel.PHP.AjaxPager("%s", "%s"); });', $replaceId, $pagerClass);
    $buf[] = '</script>';

    return join($buf, "")."\n";
  }

  public static function formValidator($formObj, $errBox = "sbl_errmsg")
  {
    $include = <<<INC
<script type="text/javascript" src="%s" charset="UTF-8"></script>
INC;

    $model   = $formObj->getModel();
    $columns = $model->getColumns();
    $errMsgs = Sabel_DB_Validate_Config::getMessages();
    $lNames  = Sabel_DB_Model_Localize::getColumnNames($model->getName());

    $data = array("data" => array(), "errors" => $errMsgs);
    foreach ($columns as $c) {
      $name = $c->name;
      $c->name = $lNames[$c->name];
      $data["data"][$name] = array_change_key_case((array) $c, CASE_UPPER);
    }

    $buf   = array();
    $buf[] = sprintf($include, linkTo("js/helpers/FormValidator.js"));

    $buf[] = "\n";

    $buf[] = '<script type="text/javascript">';
    $buf[] = 'new Sabel.PHP.FormValidator('.json_encode($data).');';
    $buf[] = '</script>';

    return join($buf, "")."\n";
  }
}
