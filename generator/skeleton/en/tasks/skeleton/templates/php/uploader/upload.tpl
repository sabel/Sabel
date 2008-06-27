<script type="text/javascript" src="<#php echo linkto("js/helpers/AjaxUploader.js") #>"></script>

<style type="text/css">
@import url("<#php echo linkto("js/helpers/css/Sabel.css") #>");
</style>

<div id="progressBar"></div>

<form id="upload_form" enctype="multipart/form-data" action="<#php echo uri("a: uploaded") #>" method="post">
  <p>
    <input type="hidden" name="APC_UPLOAD_PROGRESS" value="<#php echo $uploadId #>" />
    <input type="file"   name="upfile" /><br />
    <input type="submit" value="upload" />
  </p>
</form>

<script type="text/javascript">
new Sabel.PHP.AjaxUploader("upload_form", "<#php echo uri("a: fetchStatus") #>?uploadId=<#php echo $uploadId #>", "progressBar");
</script>
