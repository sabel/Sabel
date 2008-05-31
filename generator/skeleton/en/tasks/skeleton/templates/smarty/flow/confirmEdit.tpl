<h2>Edit <?php echo $mdlName ?></h2>

{php}echo $this->get_template_vars("renderer")->partial("_confirm", array("postAction" => "edit", "correctAction" => "correctEdit")){/php}
