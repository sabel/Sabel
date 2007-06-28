<?php

function validateEmailAddress($address, $name)
{
  if ($address !== null) {
    $result = preg_match("/^[\w.\-_]+@([\w\-_]+\.)+[a-zA-Z]+$/", $address);

    if ($result === 0) {
      return "invalid $name format.";
    }
  }
}
