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
$projresponse->Key = $component->ID;
$projresponse->Name = $component->cname;
$projresponse->Class = 'ProjectComponent';
$projresponse->Description = $component->description;
$projresponse->url=$component->URL;
$projresponse->lead=$component->LEAD;


$projresponse->self = new stdClass();
$projresponse->self->href = $_GET['sitemapbase'].qs2sef("comp={$component->ID}&proj={$component->pkey}",".json");
$projresponse->self->type = 'application/json';
$projresponse->self->alternate = array();
$projresponse->self->alternate[0]->type = 'text/html';
$projresponse->self->alternate[0]->href = $_GET['sitemapbase'].qs2sef("comp={$component->ID}&proj={$component->pkey}");

$projresponse->parent = new stdClass();
$projresponse->parent->Class='Project';
$projresponse->parent->href=$_GET['sitemapbase'].qs2sef("proj={$component->pkey}",".json");
$projresponse->parent->alternate = array();
$projresponse->parent->alternate[0]->type = 'text/html';
$projresponse->parent->alternate[0]->href = $_GET['sitemapbase'].qs2sef("proj={$component->pkey}");

include 'templates/json/issues-table.php';
$projresponse->issues = $issueobj;

echo json_encode($projresponse);


