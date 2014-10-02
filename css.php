<?php
/** JIRA Issue List Script
*
* Simple script to generate an simple HTML listing of JIRA Issues from a Private JIRA Instance
* Intended use is to allow indexing of JIRA issues by internal search engines such as Sphider (http://www.sphider.eu/)
*
* Documentation: http://www.bentasker.co.uk/documentation/development-programming/273-allowing-your-internal-search-engine-to-index-jira-issues
*
* @copyright (C) 2014 B Tasker (http://www.bentasker.co.uk). All rights reserved
* @license GNU GPL V2 - See LICENSE
*
* @version 1.2
*
*/

defined('listpage') or die;
?>

blockquote{border-left: 1px solid black;
padding-left: 10px;
background: lightgray;
padding-bottom: 10px;
word-wrap: break-word;
}

pre{border-radius: 3px 3px 3px 3px;
margin: 9px 0;
border: 1px solid #cccccc;
background: #f5f5f5;
font-size: 12px;
line-height: 1.33333333333333;
font-family: monospace;
word-wrap: break-word;
}


.commentlink {font-size: 0.7em; float: right;}
.statechangetext {font-style: italic;}
.commenttext, .issuedescription {font-family: monospace}
table.issueInfo {width: 100%; border: 0px;}
.reporter {font-style: italic; }

.statusOpen {color: red}
.statusClosed {color: green}

.pty4, .pty5 {color: green;}
.pty3 {color: red; }
.pty1, .pty2 {color: red; font-weight: bolder; }
.prevlink {float:left;}
.nextlink {float:right;}
.prevlink a {text-decoration: none;}
.nextlink a {text-decoration: none;}
.worklogindex {font-style: italic;}
.timespent {font-weight: bold;}

#worklogblock, #commentsblock, #subtasksblock, #linksblock, #attachmentsblock { border: 1px solid #000; padding: 10px; margin-top: 40px}
#attachmentsblock table {width: 40%}
#attachmentsblock img {margin: 5px;}

.favicon {max-width: 20px;}
.issuelistingtable td {text-align: center;}
.issuelistingtable {width: 70%;}

