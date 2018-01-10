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
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Search</title>
	<meta name="description" content="Search">
        <link rel="alternate" type="application/json" href="sitemap.json">
        <script src="static/search.js" type="text/javascript"></script>
	<?php require 'head-includes.php'; ?>
	</head>
	<body>

	<h1>Search</h1>
	
	
	
<form id="searchform" onsubmit="return handleFormSubmit();">
    <input type="text" id="searchterms" value="" ><input type="Submit" value="Search">
</form>

<h3 id="searchtitle" style="display: none">Results for <span id='searchterm'></span></h3>

<div id="resultswrapper"></div>

<script type="text/javascript">doAutoSearch();</script>
</div>

	
	
        </body>
</html>
