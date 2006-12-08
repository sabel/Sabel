<?php

candidate("default",
          ":controller/:action/:id",
          array("default" => array(":controller" => "index",
                                   ":action"     => "index",
                                   ":id"         => null),
                "module"  => "index"));