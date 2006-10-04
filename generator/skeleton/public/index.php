<?php

require_once('../setup.php');
$container = Container::create();
$front     = $container->load('sabel.controller.Front');
$response  = $front->ignition();
echo $response['html'];