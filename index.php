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

apply_filters();

parseSEF();
$db = new BTDB;
$authip = checkIPs();


if (isset($_GET['checkstatus'])){
	// No unauthorised access
	if (!$conf->debug && (!in_array($_SERVER['HTTP_USER_AGENT'],$conf->SphiderUA) || !$authip)){
		die;
	}
	check_status(); 
	die;
}



if (isset($_GET['rendersitemap'])){

	if (!$conf->debug && (!in_array($_SERVER['HTTP_USER_AGENT'],$conf->SphiderUA) || !$authip)){
		die;
	}

      $inc_ok = true;
      require 'templates/xml/sitemap.php';
      die;

}


if (isset($_GET['rendermovedissues'])){

	if (!$conf->debug && (!in_array($_SERVER['HTTP_USER_AGENT'],$conf->SphiderUA) || !$authip)){
		die;
	}

      $inc_ok = true;
      require 'templates/html/movedissues.php';
      die;

}


if (isset($_GET['attachid'])){

	// No unauthorised access
	if (!$conf->debug && (!in_array($_SERVER['HTTP_USER_AGENT'],$conf->SphiderUA) || !$authip)){
		die;
	}
	$inc_ok = true;
	require 'attachment.php';
	die;
}


if (isset($_GET['vers'])){
	require 'queries/version-issues.php';

	if ($_GET['reqformat'] == "json"){
		require 'templates/json/version-issues.php';
	}else{
		require 'templates/html/version-issues.php';
	}
	die;
}

if (isset($_GET['comp'])){
	require 'queries/component-issues.php';

	if ($_GET['reqformat'] == "json"){
		require 'templates/json/component-issues.php';
	}else{
		require 'templates/html/component-issues.php';
	}
	die;
}



if (!isset($_GET['issue']) || empty($_GET['issue'])):

	if (!isset($_GET['proj']) || empty($_GET['proj'])){
		// Load the list of all issues
		require 'queries/projects.php';

		if ($_GET['reqformat'] == "json"){
			require 'templates/json/projects.php';
		}else{
			require 'templates/html/projects.php';
		}


	/*}elseif ($_GET['kanban']){
		require 'project-index-kanban.php';*/
	}else{
		require 'queries/project-index.php';

		if ($_GET['reqformat'] == "json"){
			require 'templates/json/project-index.php';
		}else{
			require 'templates/html/project-index.php';
		}
	}


else:
	require 'queries/issue_page.php';

	if ($_GET['reqformat'] == "json"){

		if ($issue->moved){
			require 'templates/json/movedissue.php';
		}else{
			require 'templates/json/issue_page.php';
		}

	}elseif($_GET['reqformat'] == "txt"){
		require 'templates/text/issue_page.php';
	}else{

		if ($issue->moved){
			require 'templates/html/movedissue.php';
		}else{
			require 'templates/html/issue_page.php';
		}
	}



endif;
