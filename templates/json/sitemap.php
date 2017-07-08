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
$projresponse->Name = 'PagesList';
$projresponse->self = new stdClass();
$projresponse->self->href = $_GET['sitemapbase'].qs2sef('sitemap','.json');
$projresponse->self->type = 'application/json';
$projresponse->self->alternate = array();
$projresponse->self->alternate[0]->type = 'text/xml';
$projresponse->self->alternate[0]->href = $_GET['sitemapbase'].qs2sef('sitemap.xml');
$projresponse->items = array();


$sql = "SELECT ID, pname, pkey, DESCRIPTION from project ";

$filter = buildProjectFilter(); // See JILS-12
if ($filter){
    $sql .= "WHERE ".$filter;
}

$sql .= ' ORDER BY pkey ASC';

$db->setQuery($sql);
$projects = $db->loadResults();


/** Project Issues 
$sql = "SELECT a.SUMMARY, a.issuenum, b.pkey, a.RESOLUTIONDATE FROM jiraissue AS a LEFT JOIN project AS b on a.PROJECT = b.ID ";
$filter = buildProjectFilter('b');
if ($filter){
    $sql .= "WHERE ".$filter;
}

$sql .= ' ORDER BY a.PROJECT, a.issuenum ASC';

$db->setQuery($sql);
$issues = $db->loadResults();
*/

$sql = "SELECT a.SUMMARY, a.ID, a.issuenum, a.REPORTER, b.pname, b.pkey, c.pname as status, d.pname as resolution, e.pname as issuetype, f.pname as priority,".
"a.CREATED, a.RESOLUTIONDATE, a.TIMESPENT, f.SEQUENCE as ptysequence, a.ASSIGNEE ".
"FROM jiraissue AS a ".
"LEFT JOIN project AS b on a.PROJECT = b.ID ".
"LEFT JOIN issuestatus AS c ON a.issuestatus = c.id ".
"LEFT JOIN resolution AS d ON a.RESOLUTION = d.ID ".
"LEFT JOIN issuetype AS e ON a.issuetype = e.ID ".
"LEFT JOIN priority AS f ON a.PRIORITY = f.ID ";
	
if ($filter){
    $filter = buildProjectFilter('b'); // See JILS-12
    $sql .= "WHERE ".$filter;
}
" ORDER BY a.PROJECT, a.issuenum ASC" ;

$db->setQuery($sql);
$issues = $db->loadResults();



foreach ($projects as $project){
	$p = new stdClass();
	$p->Key=$project->pkey;
	$p->Name=$project->pname;
	$p->Class="Project";
	$p->Description=$project->DESCRIPTION;
	$p->href=$_GET['sitemapbase'].qs2sef("proj={$project->pkey}",".json");
	$p->alternate = array();
	$p->alternate[0]->type = 'text/html';
	$p->alternate[0]->href=$_GET['sitemapbase'].qs2sef("proj={$project->pkey}");
	$projresponse->items[] = $p;
}



foreach ($issues as $issue){
	$i = new stdClass();
	$i->Key=$issue->pkey."-".$issue->issuenum;
	$i->Name=$issue->SUMMARY;
	$i->Class="Issue";
	$i->IssueType=$issue->issuetype;
	$i->Priority=$issue->priority;
	$i->Status=$issue->status;
	$i->Resolution=$issue->resolution;
	$i->Created=strtotime($issue->CREATED);
	$i->assigneee=$issue->ASSIGNEE;

	$i->href=$_GET['sitemapbase'].qs2sef("issue={$issue->issuenum}&proj={$issue->pkey}",".json");
	$i->type="application/json";
	$i->alternate = array();
	$i->alternate[0]->type = 'text/html';
	$i->alternate[0]->href = $_GET['sitemapbase'].qs2sef("issue={$issue->issuenum}&proj={$issue->pkey}",".html");
	$projresponse->items[] = $i;
}



echo json_encode($projresponse);

