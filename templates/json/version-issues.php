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
$projresponse->Key = $version->ID;
$projresponse->Name = $version->vname;
$projresponse->Class = 'ProjectVersion';
$projresponse->Description = $version->DESCRIPTION;

$projresponse->State=($version->RELEASED)? 'Released' : 'Un-released';
$projresponse->Archived=($version->ARCHIVED);
$projresponse->ReleaseDate=strtotime($version->RELEASEDATE);

$projresponse->TimeEstimate=$timeestimate;
$projresponse->TimeLogged=$timespent;


$projresponse->self = new stdClass();
$projresponse->self->href = $_GET['sitemapbase'].qs2sef("vers={$version->ID}&proj={$version->pkey}",".json");
$projresponse->self->type = 'application/json';
$projresponse->self->alternate = array();
$projresponse->self->alternate[0]->type = 'text/html';
$projresponse->self->alternate[0]->href = $_GET['sitemapbase'].qs2sef("vers={$version->ID}&proj={$version->pkey}");

$projresponse->parent = new stdClass();
$projresponse->parent->Class='Project';
$projresponse->parent->href=$_GET['sitemapbase'].qs2sef("proj={$version->pkey}",".json");
$projresponse->parent->alternate = array();
$projresponse->parent->alternate[0]->type = 'text/html';
$projresponse->parent->alternate[0]->href = $_GET['sitemapbase'].qs2sef("proj={$version->pkey}");

include 'templates/json/issues-table.php';
$projresponse->issues = $issueobj;


$issues = $outstanding_issues;
$include_fix=true;
include 'templates/json/issues-table.php';
$projresponse->Knownissues = $issueobj;
$include_fix=false;
echo json_encode($projresponse);
