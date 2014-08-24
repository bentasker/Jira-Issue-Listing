<?php
/** JIRA Issue List Script - Attachment retrieval
*
* Simple script to generate an simple HTML listing of JIRA Issues from a Private JIRA Instance
* Intended use is to allow indexing of JIRA issues by internal search engines such as Sphider (http://www.sphider.eu/)
*
* This script is only really intended for use with the project archiving script.
*
* You'll need to chmod 755 /var/atlassian/application-data/jira
*
* Documentation: http://www.bentasker.co.uk/documentation/development-programming/273-allowing-your-internal-search-engine-to-index-jira-issues
*
* @copyright (C) 2014 B Tasker (http://www.bentasker.co.uk). All rights reserved
* @license GNU GPL V2 - See LICENSE
*
* @version 1.2
*
*/


if (!isset($inc_ok) || !$inc_ok){
	die;
}



if (!isset($_GET['thumbs'])):

	$ident = explode("-",$_GET['projectID']);
	$id = (int)$_GET['attachid'];

	

	//$_GET['attachid'] = $_GET['attachid']; no point actually doing this, just here to make for easy reference


	if (!file_exists($conf->jirahome."data/attachments/{$ident[0]}/{$ident[0]}-{$ident[1]}/{$id}")){
		header("HTTP/1.0 404 Not Found");
		die;
	}


	// Specified file exists
	$sql = "SELECT MIMETYPE, FILENAME, FILESIZE FROM fileattachment WHERE ID=".$id;
	$db->setQuery($sql);
	$details=$db->loadResult();

	header("Content-Type: {$details->MIMETYPE}");
	header("Content-Dispostion: attachment; filename={$details->FILENAME}");
	header("Content-Length: {$details->FILESIZE}");

	print file_get_contents($conf->jirahome."/data/attachments/{$ident[0]}/{$ident[0]}-{$ident[1]}/{$id}");

else:

	$ident = $_GET['projectID'];
	$id = (int)$_GET['attachid'];
	$key = $_GET['issueid'];


	if (!file_exists($conf->jirahome."/data/attachments/$ident/$key/thumbs/_thumb_{$id}.png")){
		header("HTTP/1.0 404 Not Found");
		die;
	}


	header("Content-Type: image/png");
	print file_get_contents($conf->jirahome."/data/attachments/$ident/$key/thumbs/_thumb_{$id}.png");

endif;
