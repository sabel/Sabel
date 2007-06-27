<?php

function validateEmailAddress($address)
{
  if ($address !== null) {
    $result = preg_match("/^[\w.\-_]+@([\w\-_]+\.)+[a-zA-Z]+$/", $address);

    if ($result === 0) {
      return "invalid email format.";
    }
  }
}
