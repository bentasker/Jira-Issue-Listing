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

//$tblid=mt_rand();

$issueobj = array();

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

/** For possible future use in JILS-36
	if (isset($include_fix) && $include_fix && isset($issue->vname) && isset($issue->fixver)){
		$i->FixVersion = new stdClass();
		$i->FixVersion->Key = $issue->fixver;
		$i->FixVersion->Name = $issue->vname;
		$i->FixVersion->Class = 'ProjectVersion';
		$i->FixVersion->href = $_GET['sitemapbase'].qs2sef("vers={$version->ID}&proj={$version->pkey}",".json");
		$i->FixVersion->alternate = array();
		$i->FixVersion->alternate[0]->type = 'text/html';
		$i->FixVersion->alternate[0]->href = $_GET['sitemapbase'].qs2sef("vers={$version->ID}&proj={$version->pkey}");
	}
*/
	$issueobj[] = $i;
}


if (!isset($tblid) || $tblid == 1){
	$tblid='1';
}

