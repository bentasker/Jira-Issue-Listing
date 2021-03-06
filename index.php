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
      require 'sitemap.php';
      die;

}


if (isset($_GET['rendermovedissues'])){

	if (!$conf->debug && (!in_array($_SERVER['HTTP_USER_AGENT'],$conf->SphiderUA) || !$authip)){
		die;
	}

      $inc_ok = true;
      require 'movedissues.php';
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
	}elseif ($_GET['kanban']){
		require 'project-index-kanban.php';
	}else{
		require 'project-index.php';
	}


else:
	require 'issue_page.php';
endif;
