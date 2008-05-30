<script type="text/javascript" src="{"js/helpers/AjaxUploader.js"|linkto}"></script>

<style type="text/css">
@import url({"js/helpers/css/Sabel.css"|linkto});
</style>

<form id="upload_form" enctype="multipart/form-data" action="{"a: uploaded"|uri}" method="post">
  <p>
    <input type="hidden" name="APC_UPLOAD_PROGRESS" value="{$uploadId}" />
    <input type="file"   name="upfile" /><br />
    <input type="submit" value="upload" />
  <p>
</form>

<div id="progressBar"></div>

<script type="text/javascript">
new Sabel.PHP.AjaxUploader("upload_form", "{"a: fetchStatus"|uri}?uploadId={$uploadId}", "progressBar");
</script>
