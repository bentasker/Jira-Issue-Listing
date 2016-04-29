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
$projresponse->Key = $issue->OLD_ISSUE_KEY;
$projresponse->Class = 'MovedIssue';
$projresponse->Description = "Issue {$issue->OLD_ISSUE_KEY} has moved to {$issue->pkey}-{$issue->issuenum}";
//$projresponse->LastModified = $dstring; // From etag generation - for moved issues this is hardcoded so removed as misleading

$projresponse->NewLocation = new stdClass();
$projresponse->NewLocation->href = $_GET['sitemapbase'].qs2sef("issue={$issue->issuenum}&proj={$issue->pkey}",".json");
$projresponse->NewLocation->type = 'application/json';
$projresponse->NewLocation->alternate = array();
$projresponse->NewLocation->alternate[0]->type = 'text/html';
$projresponse->NewLocation->alternate[0]->href = $_GET['sitemapbase'].qs2sef("issue={$issue->issuenum}&proj={$issue->pkey}");


$projresponse->self= new stdClass();
$projresponse->self->href = $_GET['sitemapbase']."/browse/{$issue->OLD_ISSUE_KEY}.json";
$projresponse->self->type = 'application/json';
$projresponse->self->alternate=array();
$projresponse->self->alternate[0]->type = 'text/html';
$projresponse->self->alternate[0]->href = $_GET['sitemapbase']."/browse/{$issue->OLD_ISSUE_KEY}.html";


echo json_encode($projresponse);
