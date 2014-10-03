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


define('listpage',1);

require 'config.php';
require 'utils.class.php';

parseSEF();
$db = new BTDB;

if (isset($_GET['attachid'])){

	// No unauthorised access
	if (!$conf->debug && (!in_array($_SERVER['HTTP_USER_AGENT'],$conf->SphiderUA) || !checkIPs())){
		die;
	}
	$inc_ok = true;
	require 'attachment.php';
	die;
}


if (isset($_GET['vers'])){
	require 'version-issues.php';
	die;
}

if (isset($_GET['comp'])){
	require 'component-issues.php';
	die;
}



if (!isset($_GET['issue']) || empty($_GET['issue'])):

	if (!isset($_GET['proj']) || empty($_GET['proj'])){
		// Load the list of all issues
		require 'projects.php';
	}else{
		require 'project-index.php';
	}


else:
	require 'issue_page.php';
endif;
