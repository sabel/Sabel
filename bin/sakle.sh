#!/bin/sh

if [ -z "$SABEL_HOME" ] ; then
  SABEL_HOME="@PEAR-DIR@"
fi

if (test -z "$PHP_COMMAND") ; then
  export PHP_COMMAND=php
fi

if (test -z "$PHP_CLASSPATH") ; then
  PHP_CLASSPATH=$SABEL_HOME/lib
  export PHP_CLASSPATH
fi

$PHP_COMMAND -d html_errors=off -qC $SABEL_HOME/Sabel/sabel/Sakle.php $*