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
$projresponse->Key = "{$issue->pkey}-{$issue->issuenum}";
$projresponse->Name = $issue->SUMMARY;
$projresponse->Class = 'Issue';
$projresponse->Description = textProcessMarkup($issue->DESCRIPTION);
$projresponse->LastModified = $dstring; // From etag generation
$projresponse->IssueType=$issue->issuetype;
$projresponse->Priority=$issue->priority;
$projresponse->Status=$issue->status;
$projresponse->Resolution=$issue->resolution;
$projresponse->Created=strtotime($issue->CREATED);
$projresponse->assigneee=$issue->ASSIGNEE;
$projresponse->Reporter=$issue->REPORTER;
$projresponse->Environment=$issue->ENVIRONMENT;
$projresponse->TimeEstimate=$TIMEESTIMATE;
$projresponse->TimeLogged=$TIMESPENT;

$projresponse->AffectsVersions = array();
foreach ($affectsversions as $af){
	$p = new stdClass();
	$p->Key=$af->ID;
	$p->Name=$af->vname;
	$p->Class="Version";
	$p->href=$_GET['sitemapbase'].qs2sef("vers={$af->ID}&proj={$issue->pkey}",".json");
	$p->alternate = array();
	$p->alternate[0]->type = 'text/html';
	$p->alternate[0]->href=$_GET['sitemapbase'].qs2sef("vers={$af->ID}&proj={$issue->pkey}");
	$projresponse->AffectsVersions[] = $p;
}

$projresponse->FixVersions = array();
foreach ($fixversions as $af){
	$p = new stdClass();
	$p->Key=$af->ID;
	$p->Name=$af->vname;
	$p->Class="Version";
	$p->href=$_GET['sitemapbase'].qs2sef("vers={$af->ID}&proj={$issue->pkey}",".json");
	$p->alternate = array();
	$p->alternate[0]->type = 'text/html';
	$p->alternate[0]->href=$_GET['sitemapbase'].qs2sef("vers={$af->ID}&proj={$issue->pkey}");
	$projresponse->FixVersions[] = $p;
}

$projresponse->components = array();
foreach ($components as $af){
	$p = new stdClass();
	$p->Key=$af->ID;
	$p->Name=$af->cname;
	$p->Class="Component";
	$p->href=$_GET['sitemapbase'].qs2sef("comp={$af->ID}&proj={$issue->pkey}",".json");
	$p->alternate = array();
	$p->alternate[0]->type = 'text/html';
	$p->alternate[0]->href=$_GET['sitemapbase'].qs2sef("comp={$af->ID}&proj={$issue->pkey}");
	$projresponse->components[] = $p;
}




$projresponse->labels=array();
foreach ($labels as $label){
	$projresponse->labels[] = $label;
}



$projresponse->self = new stdClass();
$projresponse->self->href = $_GET['sitemapbase'].qs2sef("issue={$issue->issuenum}&proj={$issue->pkey}",".json");
$projresponse->self->type = 'application/json';
$projresponse->self->alternate = array();
$projresponse->self->alternate[0]->type = 'text/html';
$projresponse->self->alternate[0]->href = $_GET['sitemapbase'].qs2sef("issue={$issue->issuenum}&proj={$issue->pkey}");

$projresponse->Previous= new stdClass();
$projresponse->Previous->href = $_GET['sitemapbase'].qs2sef("issue={$previssue->issuenum}&proj={$previssue->pkey}",".json");
$projresponse->Previous->type = 'application/json';
$projresponse->Previous->Key = "{$previssue->pkey}-{$previssue->issuenum}";
$projresponse->Previous->alternate=array();
$projresponse->Previous->alternate[0]->type = 'text/html';
$projresponse->Previous->alternate[0]->href = $_GET['sitemapbase'].qs2sef("issue={$previssue->issuenum}&proj={$previssue->pkey}");


$projresponse->Next= new stdClass();
$projresponse->Next->href = $_GET['sitemapbase'].qs2sef("issue={$nextissue->issuenum}&proj={$nextissue->pkey}",".json");
$projresponse->Next->type = 'application/json';
$projresponse->Next->Key = "{$previssue->pkey}-{$previssue->issuenum}";
$projresponse->Next->alternate=array();
$projresponse->Next->alternate[0]->type = 'text/html';
$projresponse->Next->alternate[0]->href = $_GET['sitemapbase'].qs2sef("issue={$nextissue->issuenum}&proj={$nextissue->pkey}");



if ($parent){
	$projresponse->parent = new stdClass();
	$projresponse->parent->Class='Issue';
	$projresponse->parent->href=$_GET['sitemapbase'].qs2sef("issue={$parent->issuenum}&proj={$parent->pkey}",".json");
	$projresponse->parent->alternate = array();
	$projresponse->parent->alternate[0]->type = 'text/html';
	$projresponse->parent->alternate[0]->href = $_GET['sitemapbase'].qs2sef("issue={$parent->issuenum}&proj={$parent->pkey}");


}else{
	$projresponse->parent = new stdClass();
	$projresponse->parent->Class='Project';
	$projresponse->parent->href=$_GET['sitemapbase'].qs2sef("proj={$issue->pkey}",".json");
	$projresponse->parent->alternate = array();
	$projresponse->parent->alternate[0]->type = 'text/html';
	$projresponse->parent->alternate[0]->href = $_GET['sitemapbase'].qs2sef("proj={$issue->pkey}");
}



$projresponse->attachments=array();
foreach ($attachments as $attachment){
	$p = new stdClass();
	$p->Name = $attachment->FILENAME;
	$p->href = $_GET['sitemapbase'].qs2sef("attachment={$attachment->ID}&fname={$attachment->FILENAME}&projid={$issue->pkey}-{$issue->issuenum}");
	$projresponse->attachments[] = $p;
}

$projresponse->Relations = new stdClass();

$projresponse->Relations->LinkedIssues=array();
foreach ($relations as $relation){
	$p = new stdClass();
	$p->Key=$relation->relatedissue->pkey."-".$relation->relatedissue->issuenum;
	$p->Name=$relation->relatedissue->SUMMARY;
	$p->Class="Issue";
	$p->RelType = ($relation->DESTINATION == $issue->ID)? $relation->INWARD : $relation->OUTWARD;
	$p->Resolved = $relation->resolved;

	$p->href=$_GET['sitemapbase'].qs2sef("issue={$relation->relatedissue->issuenum}&proj={$relation->relatedissue->pkey}",".json");
	$p->type="application/json";
	$p->alternate = array();
	$p->alternate[0]->type = 'text/html';
	$p->alternate[0]->href = $_GET['sitemapbase'].qs2sef("issue={$relation->relatedissue->issuenum}&proj={$relation->relatedissue->pkey}",".html");;
	$projresponse->Relations->LinkedIssues[] = $p;
}

$projresponse->Relations->RelatedLinks=array();
foreach ($relationsext as $relation){
	$p = new stdClass();
	$p->Icon = $relation->ICONURL;
	$p->href = $relation->URL;
	$p->title = $relation->TITLE;
	$projresponse->Relations->RelatedLinks[] = $p;
}

$projresponse->Relations->SubTasks=array();
foreach ($subtasks as $relation){
	$p = new stdClass();
	$p->Key=$relation->relatedissue->pkey."-".$relation->relatedissue->issuenum;
	$p->Name=$relation->relatedissue->SUMMARY;
	$p->TimeEstimate=$relation->relatedissue->TIMEESTIMATE;
	$p->TimeLogged=$relation->relatedissue->TIMESPENT;
	$p->Class="Issue";
	$p->href=$_GET['sitemapbase'].qs2sef("issue={$relation->relatedissue->issuenum}&proj={$relation->relatedissue->pkey}",".json");
	$p->type="application/json";
	$p->alternate = array();
	$p->alternate[0]->type = 'text/html';
	$p->alternate[0]->href = $_GET['sitemapbase'].qs2sef("issue={$relation->relatedissue->issuenum}&proj={$relation->relatedissue->pkey}",".html");;
	$projresponse->Relations->SubTasks[] = $p;
}

$projresponse->Comments=new stdClass();
$projresponse->Comments->count = $comment_count;
$projresponse->Comments->items = array();

$projresponse->StateChanges=new stdClass();
$projresponse->StateChanges->items = array();

foreach ($commentsmerged as $comment){

	$ele = ($comment->rowtype == 'comment')? 'Comments' : 'StateChanges';
	$p = new stdClass();
	$p->Key = $comment->ID;
	$p->Author = $comment->AUTHOR;
	$p->Created = strtotime($comment->CREATED);
	$p->body = textProcessMarkup($comment->actionbody);
	$p->href=null;
	$p->alternate = array();
	$p->alternate[0]->type = 'text/html';
	$p->alternate[0]->href=$_GET['sitemapbase'].qs2sef("issue={$issue->issuenum}&proj={$issue->pkey}")."#comment".$comment->ID;
	$projresponse->$ele->items[] = $p;
}
$projresponse->StateChanges->count = count($projresponse->StateChanges->items);

$projresponse->WorkLog = array();
foreach ($worklog as $work){
	$p = new stdClass();
	$p->Key = $work->ID;
	$p->Author = $work->AUTHOR;
	$p->Created = $work->CREATED;
	$p->timelogged = $work->timeworked;
	$p->description = textProcessMarkup($work->worklogbody);
	$p->href=null;
	$p->alternate = array();
	$p->alternate[0]->type = 'text/html';
	$p->alternate[0]->href=$_GET['sitemapbase'].qs2sef("issue={$issue->issuenum}&proj={$issue->pkey}")."#worklog".$work->ID;
	$projresponse->WorkLog[] = $p;
}


echo json_encode($projresponse);
