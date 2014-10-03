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
$db->setQuery($sql);
$version = $db->loadResult();


$sql = "SELECT DISTINCT a.ID, a.SUMMARY, a.issuenum, a.REPORTER, b.pname, b.pkey, c.pname as status, d.pname as resolution, e.pname as issuetype, f.pname as priority,".
	"a.CREATED, a.RESOLUTIONDATE, a.TIMESPENT, f.SEQUENCE as ptysequence ".
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
	"ORDER BY a.PROJECT, a.issuenum ASC" ;

$db->setQuery($sql);
$issues = $db->loadResults();

?>
<html>
<head>
<title>Version: <?php echo htmlspecialchars($version->vname); ?></title>
<style type="text/css">
<?php require 'css.php'; ?>
</style>
<?php require 'head-includes.php'; ?>
</head>
<body>

<h1>Version: <?php echo htmlspecialchars($version->vname); ?></h1>

<table class="versinfotable">
	<tr>
		<th>Project</th><td><a href="<?php echo qs2sef("proj={$version->pkey}");?>"><?php echo $version->pkey;?></a></td>
	</tr>
	<tr>
		<th>Description</th><td><?php echo htmlspecialchars($version->DESCRIPTION); ?></td>
	</tr>
	<tr>
		<th>Status</th><td><?php echo ($version->RELEASED)? 'Released' : 'Un-released'; ?></td>
	</tr>
	<tr>
		<th></th><td><?php echo (!empty($version->RELEASEDATE))? $version->RELEASEDATE : '' ;?></td>
	</tr>
</table>

<hr />
<!--sphider_noindex-->
<h3>Issues</h3>
<?php include 'issues-table.php'; ?>

<!--/sphider_noindex-->

</body>
</html>

