<?php

$plugin = Sabel_Plugin::create();

$plugin->add(new Sabel_Plugin_Common());
$plugin->add(new Sabel_Plugin_View());
$plugin->add(new Sabel_Plugin_Redirecter());
$plugin->add(new Sabel_Plugin_Exception());
