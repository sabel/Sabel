<?php

candidate("default",
          ":controller/:action/:id",
          array("default" => array(":id" => null),
                "module"  => "index"));