<?php

function isString($arg)
{
  return (is_string($arg) || $arg instanceof Sabel_Util_String);
}

function isArray($arg)
{
  return (is_string($arg) || $arg instanceof ArrayAccess);
}
