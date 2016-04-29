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


if (!$conf->debug && (!in_array($_SERVER['HTTP_USER_AGENT'],$conf->SphiderUA) || !checkIPs())){
	// Redirect real users to JIRA
	header("Location: {$conf->jiralocation}/browse/{$_GET['proj']}-{$_GET['issue']}");
	die;
}



$sql = "SELECT a.SUMMARY, a.ID, a.issuenum, a.DESCRIPTION, a.REPORTER, b.pname, b.pkey, c.pname as status, d.pname as resolution, e.pname as issuetype, f.pname as priority,".
	"a.CREATED, a.RESOLUTIONDATE, a.TIMESPENT, a.TIMEESTIMATE, a.TIMEORIGINALESTIMATE, f.SEQUENCE as ptysequence, a.ENVIRONMENT, a.ASSIGNEE ".
	"FROM jiraissue AS a ".
	"LEFT JOIN project AS b on a.PROJECT = b.ID ".
	"LEFT JOIN issuestatus AS c ON a.issuestatus = c.id ".
	"LEFT JOIN resolution AS d ON a.RESOLUTION = d.ID ".
	"LEFT JOIN issuetype AS e ON a.issuetype = e.ID ".
	"LEFT JOIN priority AS f ON a.PRIORITY = f.ID ".
	"WHERE a.issuenum='".$db->stringEscape($_GET['issue']) . 
	"' AND b.pkey='".$db->stringEscape($_GET['proj'])."'";

	$filter = buildProjectFilter('b'); // See JILS-12
	if ($filter){
	    $sql .= " AND ".$filter;
	}


$db->setQuery($sql);
$issue = $db->loadResult();

if (!$issue){

    // Check whether it's a moved issue
    $sql = "SELECT a.OLD_ISSUE_KEY, b.pname, b.pkey, ji.issuenum FROM `moved_issue_key` AS a ".
    "LEFT JOIN jiraissue AS ji ON a.ISSUE_ID = ji.ID ".
    "LEFT JOIN project AS b on ji.PROJECT = b.ID ".
    "WHERE a.OLD_ISSUE_KEY='". $db->stringEscape($_GET['proj'].'-'.$_GET['issue'])."' ";
    $filter = buildProjectFilter('b'); // See JILS-12
    if ($filter){
	$sql .= " AND ".$filter;
    }

    $db->setQuery($sql);
    $issue = $db->loadResult();

    if ($issue){
	$issue->moved = true;
	return;
    }



    header("HTTP/1.0 404 Not Found",true,404);
    echo "ISSUE NOT FOUND";
    die;
}

$issue->moved = false;

/* Get the dates the issue was last modified, and return a Last-Modified header. Also include an etag based on the dates
Could look at adding support for conditional requests at some point, for now simply want to allow search engine sphider to identify whether a page has changed from a HEAD */
$sql = "SELECT max(a.CREATED) as maxcreate FROM `changegroup` AS a LEFT JOIN `changeitem` AS b on a.ID = b.groupid WHERE a.issueid=".(int)$issue->ID.
" ORDER BY a.created ASC";

$db->setQuery($sql);
$changes = $db->loadResult();

$sql = "SELECT max(CREATED) as maxcreate FROM jiraaction where issueid=".(int)$issue->ID." ORDER BY CREATED ASC";
$db->setQuery($sql);
$comments = $db->loadResult();

if (strtotime($changes->maxcreate) > strtotime($comments->maxcreate)){
      $dstring=gmdate('D, d M Y H:i:s T',strtotime($changes->maxcreate));
}else{
      $dstring=gmdate('D, d M Y H:i:s T',strtotime($comments->maxcreate));
}

$etag='"i'.$issue->ID."-".md5($changes->maxcreate.$comments->maxcreate."f:".$_GET['reqformat']).'"';

header("Last-Modified: $dstring");
header("ETag: $etag");

// Introduced in JILS-41
evaluateConditionalRequest($dstring,$etag);

if (stripos($_SERVER['REQUEST_METHOD'], 'HEAD') !== FALSE) {
       	exit();
}


// Get Previous Issue
$sql = "SELECT a.issuenum, a.SUMMARY, b.pkey FROM jiraissue as a ".
      "LEFT JOIN project as b on a.PROJECT = b.ID ".
      "WHERE a.CREATED < '".$db->stringEscape($issue->CREATED)."' ".
      " AND b.pkey='".$db->stringEscape($_GET['proj'])."'".
      "ORDER BY a.CREATED DESC";
$db->setQuery($sql);
$previssue = $db->loadResult();


// Get Next Issue
$sql = "SELECT a.issuenum, a.SUMMARY, b.pkey FROM jiraissue as a ".
      "LEFT JOIN project as b on a.PROJECT = b.ID ".
      "WHERE a.CREATED > '".$db->stringEscape($issue->CREATED)."' ".
      " AND b.pkey='".$db->stringEscape($_GET['proj'])."'".
      "ORDER BY a.CREATED ASC";
$db->setQuery($sql);
$nextissue = $db->loadResult();



$sql = "SELECT ID, actionbody, CREATED, AUTHOR FROM jiraaction where actiontype='comment' AND issueid=".(int)$issue->ID." ORDER BY CREATED ASC";
$db->setQuery($sql);
$comments = $db->loadResults();



// Get Relations
$sql = "select a.*,b.* from issuelink as a LEFT JOIN issuelinktype as b ON a.LINKTYPE=b.ID WHERE (a.SOURCE=".(int)$issue->ID . " OR a.DESTINATION=".(int)$issue->ID .
") AND b.OUTWARD != 'jira_subtask_outward'";
$db->setQuery($sql);
$relations = $db->loadResults();

// Get Subtasks
$sql = "select a.*,b.* from issuelink as a LEFT JOIN issuelinktype as b ON a.LINKTYPE=b.ID WHERE a.SOURCE=".(int)$issue->ID .
" AND b.OUTWARD = 'jira_subtask_outward'";
$db->setQuery($sql);
$subtasks = $db->loadResults();



// Get external links
$sql = "SELECT * FROM remotelink WHERE ISSUEID=".(int)$issue->ID;
$db->setQuery($sql);
$relationsext = $db->loadResults();

if (count($relationsext) < 1){ // Make sure we don't generate a NOTICE later
  $relationsext = array();
}


// Get Attachments
$sql = "select * from fileattachment where issueid=".(int)$issue->ID." ORDER BY CREATED ASC";
$db->setQuery($sql);
$attachments = $db->loadResults();

// Get Workflow (exclude time estimate and timespent - we'll deal with those later)
$sql = "SELECT a.CREATED, a.AUTHOR, b.* FROM `changegroup` AS a LEFT JOIN `changeitem` AS b on a.ID = b.groupid WHERE a.issueid=".(int)$issue->ID.
" AND b.FIELD IN ('status','resolution','assignee','Fix Version','Version','labels','Attachment','priority','timespent','Project','Key')".
" AND a.CREATED > '".$db->stringEscape($issue->CREATED)."' ORDER BY a.created ASC";
$db->setQuery($sql);
$workflow = $db->loadResults();


// Merge the workflow with comments, first bit's easy
$commentsmerged = array();
$comment_authors = array();
$comment_count=0;
foreach ($comments as $comment){
    $t = strtotime($comment->CREATED);
    $comment->rowtype = 'comment';
    $comment_authors[] = $comment->AUTHOR;
    // Don't overwrite a previous comment if two people commented at the same time. Increment the array key by one until we find a free one
    while (true){
	$k = "a".$t;
	if (!isset($commentsmerged[$k])){
	    $commentsmerged[$k] = $comment;
	    break;
	}
	$t++;
    }

    $comment_count++;
}

// Now we need to process the workflow and turn it into comments
foreach ($workflow as $wf){
    $t = strtotime($wf->CREATED);
    $co = new stdClass();
    $co->ID = 'wf'.$wf->ID;
    $co->AUTHOR = '';
    $co->CREATED = $wf->CREATED;
    $comment_authors[] = $wf->AUTHOR;
    if (!empty($wf->NEWSTRING)){

	  // Tweak the value depending on field type
	  switch ($wf->FIELD){
		case 'timespent':
		      $wf->NEWSTRING = ($wf->NEWSTRING / 60) . " minutes";
		      $wf->OLDSTRING = ($wf->OLDSTRING / 60) . " minutes";
		break;

		case 'Attachment':
		      $wf->FIELD = 'Attachments';
		break;

		case 'priority':
		      $sql = "SELECT SEQUENCE FROM priority WHERE ID=".(int)$wf->NEWVALUE;
		      $db->setQuery($sql);
		      $s = $db->loadResult();

		      $wf->NEWSTRING = '[issuelistpty '. $s->SEQUENCE .']'.$wf->NEWSTRING.'[/issuelistpty]';

		      if (!empty($wf->OLDSTRING)){
			  $sql = "SELECT SEQUENCE FROM priority WHERE ID=".(int)$wf->OLDVALUE;
			  $db->setQuery($sql);
			  $s = $db->loadResult();

			  $wf->OLDSTRING = '[issuelistpty '. $s->SEQUENCE .']'.$wf->OLDSTRING.'[/issuelistpty]';
		      }
		break;

	  }


	  if (!empty($wf->OLDSTRING)){
	    $co->actionbody = "{$wf->AUTHOR} changed {$wf->FIELD} from '{$wf->OLDSTRING}' to '{$wf->NEWSTRING}'";
	  }else{
	    $co->actionbody = "{$wf->AUTHOR} added '{$wf->NEWSTRING}' to {$wf->FIELD}";
	  }

    }else{
	  $co->actionbody = "{$wf->AUTHOR} removed '{$wf->OLDSTRING}' from {$wf->FIELD}";
    }

    $co->rowtype = 'statechange';

    while (true){
      $k= "a".$t;
      if (!isset($commentsmerged[$k])){
	  $commentsmerged[$k] = $co;
	  break;
      } 
      $t++;
    }
}

$authors_list = array_unique($comment_authors);

// Sort by timestamp
ksort($commentsmerged);

// Get Versions
$sql = "SELECT b.vname, b.ID FROM nodeassociation AS a ".
	"LEFT JOIN projectversion AS b on a.SINK_NODE_ID = b.ID ".
	"WHERE a.SOURCE_NODE_ID=".(int)$issue->ID." AND a.ASSOCIATION_TYPE='IssueVersion'";
$db->setQuery($sql);
$affectsversions = $db->loadResults();


// Get Components
$sql = "SELECT a.SOURCE_NODE_ID, b.cname, b.ID FROM nodeassociation AS a ".
	"LEFT JOIN component AS b on a.SINK_NODE_ID = b.ID ".
	"WHERE a.SOURCE_NODE_ID=".(int)$issue->ID." AND a.ASSOCIATION_TYPE='IssueComponent'";
$db->setQuery($sql);
$components = $db->loadResults();


// Get Target Fix version
$sql = "SELECT b.vname, b.ID FROM nodeassociation AS a ".
"LEFT JOIN projectversion AS b on a.SINK_NODE_ID = b.ID ".
"WHERE a.SOURCE_NODE_ID=".(int)$issue->ID." AND a.ASSOCIATION_TYPE='IssueFixVersion'";
$db->setQuery($sql);
$fixversions = $db->loadResults();


// Get Labels
$sql = "SELECT LABEL FROM `label` WHERE ISSUE=".(int)$issue->ID;
$db->setQuery($sql);
$labels = $db->loadResults();

// Get the Worklog
$sql = "SELECT * FROM worklog WHERE issueid=".(int)$issue->ID . " ORDER BY CREATED ASC";
$db->setQuery($sql);
$worklog = $db->loadResults();

$resolution = (empty($issue->resolution))? 'Unresolved' : $issue->resolution. " ({$issue->RESOLUTIONDATE})";



// Implemented for JILS-32
$TIMESPENT = $issue->TIMESPENT;
$TIMEESTIMATE = $issue->TIMEESTIMATE;
$TIMEORIGINALESTIMATE = $issue->TIMEORIGINALESTIMATE;

if (count($subtasks) > 0){

// Get Subtasks
$sql = "select SUM(c.TIMESPENT) as timespent, SUM(c.TIMEESTIMATE) as timeestimate, SUM(c.TIMEORIGINALESTIMATE) as timeoriginalestimate ".
"from issuelink as a ".
"LEFT JOIN issuelinktype as b ON a.LINKTYPE=b.ID ".
"LEFT JOIN jiraissue as c ON a.DESTINATION = c.ID ".
"WHERE a.SOURCE=".(int)$issue->ID .
" AND b.OUTWARD = 'jira_subtask_outward'";
$db->setQuery($sql);
$subtask_times = $db->loadResult();

$TIMESPENT = $issue->TIMESPENT + $subtask_times->timespent;
$TIMEESTIMATE = $issue->TIMEESTIMATE + $subtask_times->timeestimate;
$TIMEORIGINALESTIMATE = $issue->TIMEORIGINALESTIMATE + $subtask_times->timeoriginalestimate;

}

// Get Parent Issue
$sql = "select c.issuenum, p.pkey, c.SUMMARY from issuelink as a " .
"LEFT join jiraissue as c ON a.SOURCE = c.ID ".
"LEFT JOIN project AS p on c.PROJECT = p.ID ".
"LEFT JOIN issuelinktype as b ON a.LINKTYPE=b.ID ".
"WHERE a.DESTINATION=".(int)$issue->ID .
" AND b.INWARD = 'jira_subtask_inward'";
$db->setQuery($sql);
$parent = $db->loadResult();



foreach ($relations as $key=>$relation){

	$remid = ($relation->DESTINATION == $issue->ID)? $relation->SOURCE : $relation->DESTINATION;
	$reltype = ($relation->DESTINATION == $issue->ID)? $relation->INWARD : $relation->OUTWARD;

	$sql = "SELECT a.SUMMARY, a.issuenum, b.pkey, d.pname as resolution FROM jiraissue AS a ".
		"LEFT JOIN project AS b on a.PROJECT = b.ID ".
		"LEFT JOIN resolution AS d ON a.RESOLUTION = d.ID " .
		"WHERE a.id=".(int)$remid;
	$db->setQuery($sql);
	$relatedissue = $db->loadResult();

	$relations->$key->relatedissue = $relatedissue;
	$relations->$key->resolved = (empty($relatedissue->resolution))? false : true;

}


foreach ($subtasks as $key=>$relation){

	$remid = ($relation->DESTINATION == $issue->ID)? $relation->SOURCE : $relation->DESTINATION;
	$reltype = ($relation->DESTINATION == $issue->ID)? $relation->INWARD : $relation->OUTWARD;

	$sql = "SELECT a.SUMMARY, a.TIMESPENT, a.TIMEESTIMATE, a.TIMEORIGINALESTIMATE, a.issuenum, b.pkey, d.pname as resolution FROM jiraissue AS a ".
		"LEFT JOIN project AS b on a.PROJECT = b.ID ".
		"LEFT JOIN resolution AS d ON a.RESOLUTION = d.ID " .
		"WHERE a.id=".(int)$remid;
	$db->setQuery($sql);
	$subtasks->$key->relatedissue = $db->loadResult();
	$subtasks->$key->resolved = (empty($resolution))? false : true;

}


