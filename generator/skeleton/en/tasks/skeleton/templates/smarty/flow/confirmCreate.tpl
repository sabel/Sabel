<h2>Create <?php echo $mdlName ?></h2>

{php}echo $this->get_template_vars("renderer")->partial("_confirm", array("postAction" => "create", "correctAction" => "correctCreate")){/php}
