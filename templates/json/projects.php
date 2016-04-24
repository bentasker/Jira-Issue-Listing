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

header('Content-Type: text/json');
$projresponse = new stdClass();
$projresponse->Name = 'ProjectsList';
$projresponse->self = new stdClass();
$projresponse->self->url = 'index.json';
$projresponse->self->alturl = 'index.html';
$projresponse->items = array();

foreach ($projects as $project){
	$p = new stdClass();
	$p->Key=$project->pkey;
	$p->Name=$project->pname;
	$p->Description=$project->DESCRIPTION;
	$p->url=qs2sef("proj={$project->pkey}",".json");
	$p->alturl=qs2sef("proj={$project->pkey}");
	$projresponse->items[] = $p;
}

echo json_encode($projresponse);
