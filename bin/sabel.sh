#!/bin/sh

if [ -z "$SABEL_HOME" ] ; then
  SABEL_HOME="/usr/local/lib/php/Sabel"
fi

if (test -z "$PHP_COMMAND") ; then
  export PHP_COMMAND=php
fi

$PHP_COMMAND -d html_errors=off -qC $SABEL_HOME/generator/generator.php $*
