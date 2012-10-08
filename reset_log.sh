#!/bin/bash
WEBDIR=/home/work/local/httpd/htdocs/helpdesk
md $WEBDIR
rm -fr crontab.log ; touch crontab.log ; chmod 777 crontab.log ;
