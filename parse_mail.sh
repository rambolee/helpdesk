#!/bin/bash
WEBDIR=/home/work/local/httpd/htdocs/helpdesk
cd $WEBDIR
cd protected/
/home/work/local/php/bin/php $WEBDIR/protected/yiic parsemail
