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

if (!$conf->debug && (!in_array($_SERVER['HTTP_USER_AGENT'],$conf->SphiderUA) || !$authip)){
	// Redirect real users to JIRA
	header("Location: {$conf->jiralocation}/browse/{$_GET['proj']}/fixforversion/{$_GET['vers']}");
	die;
}



$sql = "select a.*, b.pkey from projectversion AS a LEFT JOIN project AS b ON a.PROJECT = b.ID WHERE a.ID=".(int)$_GET['vers'];

$filter = buildProjectFilter('b'); // See JILS-12
if ($filter){
    $sql .= " AND ".$filter;
}

$db->setQuery($sql);
$version = $db->loadResult();

if (!$version){
    header("HTTP/1.0 404 Not Found",true,404);
    echo "VERSION NOT FOUND";
    die;
}

$sql = "SELECT DISTINCT a.ID, a.SUMMARY, a.issuenum, a.REPORTER, b.pname, b.pkey, c.pname as status, d.pname as resolution, e.pname as issuetype, f.pname as priority,".
	"a.CREATED, a.RESOLUTIONDATE, a.TIMESPENT, f.SEQUENCE as ptysequence, a.ASSIGNEE ".
	"FROM projectversion AS pv ".
	"LEFT JOIN nodeassociation as na ON pv.ID = na.SINK_NODE_ID ".
	"LEFT JOIN jiraissue AS a ON na.SOURCE_NODE_ID = a.ID ".
	"LEFT JOIN project AS b on a.PROJECT = b.ID ".
	"LEFT JOIN issuestatus AS c ON a.issuestatus = c.id ".
	"LEFT JOIN resolution AS d ON a.RESOLUTION = d.ID ".
	"LEFT JOIN issuetype AS e ON a.issuetype = e.ID ".
	"LEFT JOIN priority AS f ON a.PRIORITY = f.ID ".
	"WHERE pv.ID='".$db->stringEscape($_GET['vers'])."' " . 
	"AND b.pkey='".$db->stringEscape($_GET['proj'])."' ".
        "AND na.ASSOCIATION_TYPE='IssueFixVersion' ".
	"ORDER BY a.PROJECT, a.issuenum ASC" ;

$db->setQuery($sql);
$issues = $db->loadResults();

// Revalidation support, introduced in JILS-41

// Capture changes to issue status
$sql = "SELECT MAX(cg.CREATED) as lastupdate ".
	"FROM projectversion AS pv ".
	"LEFT JOIN nodeassociation as na ON pv.ID = na.SINK_NODE_ID ".
	"LEFT JOIN jiraissue AS a ON na.SOURCE_NODE_ID = a.ID ".
	"LEFT JOIN project AS b on a.PROJECT = b.ID ".
	"LEFT JOIN changegroup AS cg ON a.ID = cg.issueid " .
	"LEFT JOIN changeitem AS ci ON cg.ID = ci.groupid " .
	"WHERE pv.ID='".$db->stringEscape($_GET['vers'])."' " . 
	"AND b.pkey='".$db->stringEscape($_GET['proj'])."' ";

$db->setQuery($sql);
$lastchange = $db->loadResult();
$lchange=strtotime($lastchange->lastupdate);
$dstring=gmdate('D, d M Y H:i:s T',$lchange);

// Take changes to the version record itself into account. Could do with being able to do that with Last-Mod, but haven't seen a way yet
$etag="ver-".$_GET['vers']."-".sha1("lc:$lchange;v:".json_encode($version));

header("Last-Modified: $dstring");
header("E-Tag: $etag");


// Introduced in JILS-41
evaluateConditionalRequest($dstring,$etag);

if (stripos($_SERVER['REQUEST_METHOD'], 'HEAD') !== FALSE) {
       	exit();
}


$sql = "SELECT DISTINCT a.id,pv.ID as prover FROM projectversion AS pv ".
        "LEFT JOIN nodeassociation as na ON pv.ID = na.SINK_NODE_ID ".
        "LEFT JOIN jiraissue AS a ON na.SOURCE_NODE_ID = a.ID ".
        "LEFT JOIN project AS b on a.PROJECT = b.ID ".
        "WHERE pv.ID='".$db->stringEscape($_GET['vers'])."' " . 
        "AND b.pkey='".$db->stringEscape($_GET['proj'])."' ".
        "AND na.ASSOCIATION_TYPE='IssueFixVersion' ".
        "ORDER BY a.PROJECT, a.issuenum ASC" ;
$db->setQuery($sql);
$currver = $db->loadResults();

$ids = array(0);
foreach ($currver as $c){
	$ids[] = $c->id;
}


$sql = "SELECT DISTINCT a.ID, a.SUMMARY, a.issuenum, a.REPORTER, b.pname, b.pkey, c.pname as status, d.pname as resolution, e.pname as issuetype, f.pname as priority,".
        "a.CREATED, a.RESOLUTIONDATE, a.TIMESPENT, f.SEQUENCE as ptysequence, a.ASSIGNEE ".
        "FROM projectversion AS pv ".
        "LEFT JOIN nodeassociation as na ON pv.ID = na.SINK_NODE_ID ".
        "LEFT JOIN jiraissue AS a ON na.SOURCE_NODE_ID = a.ID ".
        "LEFT JOIN project AS b on a.PROJECT = b.ID ".
        "LEFT JOIN issuestatus AS c ON a.issuestatus = c.id ".
        "LEFT JOIN resolution AS d ON a.RESOLUTION = d.ID ".
        "LEFT JOIN issuetype AS e ON a.issuetype = e.ID ".
        "LEFT JOIN priority AS f ON a.PRIORITY = f.ID ".
        "WHERE pv.ID='".$db->stringEscape($_GET['vers'])."' " . 
        "AND b.pkey='".$db->stringEscape($_GET['proj'])."' ".
	"AND na.ASSOCIATION_TYPE='IssueVersion' ".
	"AND a.ID NOT IN (" . implode(",",$ids). ") ".
        "ORDER BY a.PROJECT, a.issuenum ASC" ;

$db->setQuery($sql);
$outstanding_issues = $db->loadResults();





$sql = "SELECT SUM(a.TIMESPENT) as TIMESPENT, SUM(a.TIMEORIGINALESTIMATE) as estimate FROM projectversion AS pv ".
	"LEFT JOIN nodeassociation as na ON pv.ID = na.SINK_NODE_ID ".
	"LEFT JOIN jiraissue AS a ON na.SOURCE_NODE_ID = a.ID ".
	"LEFT JOIN project AS b on a.PROJECT = b.ID ".
	"WHERE pv.ID='".$db->stringEscape($_GET['vers'])."' " .
	"AND b.pkey='".$db->stringEscape($_GET['proj'])."' ".
	"AND na.ASSOCIATION_TYPE='IssueFixVersion' ".
	"ORDER BY a.PROJECT, a.issuenum ASC" ;

$db->setQuery($sql);
$times = $db->loadResult();

$timespent = ($times->TIMESPENT / 60);

if ($timespent > 60){
    // Convert to hours
    $timespent = round(($timespent / 60),2) . " hours";
}else{
    $timespent .= " minutes";
}
$timeestimate = ($times->estimate / 60);
if ($timeestimate > 60){
    // Convert to hours
    $timeestimate = round(($timeestimate / 60),2) . " hours";
}else{
    $timeestimate .= " minutes";
}

?>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Version: <?php echo htmlspecialchars($version->vname); ?></title>

<?php require 'head-includes.php'; ?>
</head>
<body>

<hr />
		<div id='logoarea'></div>
		<h1>Version: <?php echo htmlspecialchars($version->vname); ?></h1>
<hr />

<ul itemprop="breadcrumb" class="breadcrumbs">
      <li><a href="../../index.html">Projects</a></li>
      <li><a href="<?php echo qs2sef("proj={$version->pkey}");?>"><?php echo $version->pkey; ?></a></li>
      <li><a href="<?php echo qs2sef("vers={$version->ID}&proj={$version->pkey}");?>"><?php echo htmlspecialchars($version->vname); ?></a></li>
</ul>
<hr />

<table class="versinfotable">
	<tr>
		<th>Project</th><td><a href="<?php echo qs2sef("proj={$version->pkey}");?>"><?php echo $version->pkey;?></a></td>
	</tr>
	<tr>
		<th>Description</th><td><?php echo htmlspecialchars($version->DESCRIPTION); ?></td>
	</tr>
	<tr>
		<th>Status</th><td><?php echo ($version->RELEASED)? 'Released' : 'Un-released'; ?> 
				    <?php echo ($version->ARCHIVED)? '(Archived)':'';?></td>
	</tr>
	<tr>
		<th></th><td><?php echo (!empty($version->RELEASEDATE))? $version->RELEASEDATE : '' ;?></td>
	</tr>
	<?php if ($timeestimate > 0 ): ?>
		<tr>
			<th>Time Estimated:</th><td><?php echo $timeestimate; ?></td>
		</tr>
	<?php endif; ?>
	<?php if ($timespent > 0): ?>
		<tr>
			<th>Time Logged:</th><td><?php echo $timespent; ?></td>
		</tr>
	<?php endif; ?>
</table>

<hr />
<!--sphider_noindex-->
<h3>Issues</h3>
<?php include 'issues-table.php'; ?>

<?php if (count($outstanding_issues) > 0):?>
	<a name='buglist'></a><h3>Known Issues</h3>
	<?php $tblid = 'biscuits';
	$issues = $outstanding_issues;
	include 'issues-table.php'; ?>
<?php endif; ?>

<!--/sphider_noindex-->

</body>
</html>

