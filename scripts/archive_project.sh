#!/bin/bash
#
# Generate an offline copy of a JIRA project 
#
# TODO: Attachment support
#


JIRALIST="" # Set your JIRAlist url here

read -p "Enter project JIRA reference: " PROJECT
wget -P "tmp" -A "$PROJECT*"  -R "robots.txt" -U "Jira-Project-Archive" -p -r -k "$JIRALIST/browse/$PROJECT.html"
mv tmp/* $PROJECT
rm -rf tmp


cat << EOM > "$PROJECT/index.html"
<html>
<head>
<meta http-equiv="refresh" content="0; url=browse/$PROJECT.html">
</head>
<body>
<a href='browse/$PROJECT.html'>View the index page for Project $PROJECT</a>


<!-- Created `date` -->
</body>
</html>
EOM


