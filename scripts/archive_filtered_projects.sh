#!/bin/bash
#
# Example script to generate an offline copy of various JIRA project, filtered by keys
#
# Copyright (C) 2014 B Tasker

JIRALIST="" # Set your JIRA url here
DATEFORMAT=`date +'%Y-%m-%d-%H-%M'` 
PROJECTKEYS='VEH,GPXIN' # Comma seperate the relevant project keys


sedsafe=$(echo -n $JIRALIST | sed 's/\//\\\//g')

wget --header="X-PROJECT-LIMIT: $PROJECTKEYS" -A "$PROJECT*" -R "robots.txt" -U "Jira-Project-Archive" -r -p -k "$JIRALIST/"

find . -type f -print0 | xargs -0 sed -i "s/$sedsafe//g"
