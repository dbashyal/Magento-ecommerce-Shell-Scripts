#!/bin/sh
#
# Clean out import, session, report, tmp folders based on time
# older than n days. Housekeeping for when Magento's cron
# jobs and routines leave stuff behind

# Replace VARPATH with path to Magento var folder
# execute pwd in var directory to find that path
# for example /var/www/magento/public_html/var
#
# Supplied relative path assumes you're executing this script
# one level up from your doc root named public_html
#
# find path/* -mtime +10 -exec rm -f {} \;
# Go to a certain directory, find all files older than a certain
# time and remove them
# look for last Mod time  -mtime n = n*24hrs -mmin n = n minutes ago
# +n greater than n;  n for exactly n;  -n less than n

VARPATH='../var'
MEDIAPATH='../media'

#if [ -z "$(ls -A $VARPATH/import)" ]; then
#        echo $VARPATH/import/ empty
#else
#    find $VARPATH/import/* -mtime +1 -exec rm -f {} \;
#fi


if [ -z "$(ls -A $VARPATH/session)" ]; then
    echo $VARPATH/session/ empty
else
    find $VARPATH/session/* -mtime +4 -exec rm -f {} \;
#    ls -lA $VARPATH/session
fi

if [ -z "$(ls -A $VARPATH/report)" ]; then
    echo $VARPATH/report/ empty
else
    find $VARPATH/report/* -mtime +10 -exec rm -f {} \;
fi

if [ -z "$(ls -A $VARPATH/tmp)" ]; then
    echo $VARPATH/tmp/ empty
else
    find $VARPATH/tmp/* -mtime +1 -exec rm -f {} \;
fi

#if [ -z "$(ls -A $MEDIAPATH/import)" ]; then
#    echo $MEDIAPATH/import/ empty
#else
#    find $MEDIAPATH/import/* -mtime +1 -exec rm -f {} \;
#fi

if [ -z "$(ls -A $MEDIAPATH/tmp)" ]; then
    echo $MEDIAPATH/tmp/ empty
else
    find $MEDIAPATH/tmp/* -mtime +1 -exec rm -f {} \;
fi
