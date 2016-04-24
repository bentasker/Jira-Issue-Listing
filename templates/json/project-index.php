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
$projresponse->Key = $project->pkey;
$projresponse->Name = $project->pname;
$projresponse->Class = 'Project';
$projresponse->Description = $project->DESCRIPTION;
$projresponse->self = new stdClass();
$projresponse->self->href = $_GET['sitemapbase'].qs2sef("proj={$project->pkey}",".json");
$projresponse->self->type = 'application/json';
$projresponse->self->alternate = array();
$projresponse->self->alternate[0]->type = 'text/html';
$projresponse->self->alternate[0]->href = $_GET['sitemapbase'].qs2sef("proj={$project->pkey}");

include 'templates/json/issues-table.php';
$projresponse->issues = $issueobj;

$projresponse->components = array();

foreach ($components as $component){

	$p = new stdClass();
	$p->Name=$component->cname;
	$p->Class="Component";
	$p->Description=$component->description;
	$p->href=$_GET['sitemapbase'].qs2sef("comp={$component->ID}&proj={$project->pkey}",".json");
	$p->alternate = array();
	$p->alternate[0]->type = 'text/html';
	$p->alternate[0]->href=$_GET['sitemapbase'].qs2sef("comp={$component->ID}&proj={$project->pkey}");
	$projresponse->components[] = $p;

}


$projresponse->versions = array();

foreach ($versions as $version){

	$p = new stdClass();
	$p->Name=$version->vname;
	$p->Class="Version";
	$p->Description=$version->description;
	$p->State=($version->RELEASED)? 'Released' : 'Un-released';
	$p->Archived=($version->ARCHIVED);
	$p->ReleaseDate=strtotime($version->RELEASEDATE);
	$p->href=$_GET['sitemapbase'].qs2sef("vers={$version->ID}&proj={$project->pkey}",".json");
	$p->alternate = array();
	$p->alternate[0]->type = 'text/html';
	$p->alternate[0]->href=$_GET['sitemapbase'].qs2sef("vers={$version->ID}&proj={$project->pkey}");
	$projresponse->versions[] = $p;

}


echo json_encode($projresponse);


