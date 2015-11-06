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


// Define anything you want to add to <head>
?>
<script type="text/javascript" src="/static/tablefilter/tablefilter.js" ></script>
<script type="text/javascript" src="/static/core.js" ></script>
<link rel="stylesheet" type="text/css" href="/static/tablefilter/style/tablefilter.css" /> <!-- When mirroring with wget this is sometimes missed  if not explicitly included -->
<link rel="stylesheet" type="text/css" href="/static/core.css"/>
