<script type="text/javascript" src="<#= linkto("js/helpers/AjaxUploader.js") #>"></script>

<style type="text/css">
@import url(<#= linkto("js/helpers/css/Sabel.css") #>);
</style>

<form id="upload_form" enctype="multipart/form-data" action="<#= uri("a: uploaded") #>" method="post">
  <p>
    <input type="hidden" name="APC_UPLOAD_PROGRESS" value="<#= $uploadId #>" />
    <input type="file"   name="upfile" /><br />
    <input type="submit" value="upload" />
  <p>
</form>

<div id="progressBar"></div>

<script type="text/javascript">
new Sabel.PHP.AjaxUploader("upload_form", "<#= uri("a: fetchStatus") #>?uploadId=<#= $uploadId #>", "progressBar");
</script>
