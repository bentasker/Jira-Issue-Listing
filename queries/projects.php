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


// See whether the client IP is allowed to view us
$projdesc = null;

// Which view are we displaying?

	
	$sql = "SELECT ID, pname, pkey, DESCRIPTION from project ";

	$filter = buildProjectFilter(); // See JILS-12
	if ($filter){
	    $sql .= "WHERE ".$filter;
	}

	$sql .= ' ORDER BY pkey ASC';

	$db->setQuery($sql);
	$projects = $db->loadResults();

