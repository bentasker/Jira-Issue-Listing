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
	header("Location: {$conf->jiralocation}/browse/{$_GET['proj']}/component/{$_GET['comp']}");
	die;
}





$sql = "select a.*, b.pkey from component AS a LEFT JOIN project AS b ON a.PROJECT = b.ID WHERE a.ID=".(int)$_GET['comp'];

$filter = buildProjectFilter('b'); // See JILS-12
if ($filter){
    $sql .= " AND ".$filter;
}


$db->setQuery($sql);
$component = $db->loadResult();


if (!$component){
    header("HTTP/1.0 404 Not Found",true,404);
    echo "COMPONENT NOT FOUND";
    die;
}


$sql = "SELECT DISTINCT a.ID, a.SUMMARY, a.issuenum, a.REPORTER, b.pname, b.pkey, c.pname as status, d.pname as resolution, e.pname as issuetype, f.pname as priority,".
	"a.CREATED, a.RESOLUTIONDATE, a.TIMESPENT, f.SEQUENCE as ptysequence ".
	"FROM component AS pv ".
	"LEFT JOIN nodeassociation as na ON pv.ID = na.SINK_NODE_ID ".
	"LEFT JOIN jiraissue AS a ON na.SOURCE_NODE_ID = a.ID ".
	"LEFT JOIN project AS b on a.PROJECT = b.ID ".
	"LEFT JOIN issuestatus AS c ON a.issuestatus = c.id ".
	"LEFT JOIN resolution AS d ON a.RESOLUTION = d.ID ".
	"LEFT JOIN issuetype AS e ON a.issuetype = e.ID ".
	"LEFT JOIN priority AS f ON a.PRIORITY = f.ID ".
	"WHERE pv.ID='".$db->stringEscape($_GET['comp'])."' " . 
	"AND b.pkey='".$db->stringEscape($_GET['proj'])."'" .
	" ORDER BY a.PROJECT, a.issuenum ASC" ;

$db->setQuery($sql);
$issues = $db->loadResults();


?>
<html>
<head>
<title><?php echo $component->pkey;?> / <?php echo htmlspecialchars($component->cname); ?></title>
<?php require 'head-includes.php'; ?>
</head>
<body>

<h1><a href="<?php echo qs2sef("proj={$component->pkey}");?>"><?php echo $component->pkey;?></a> / <?php echo htmlspecialchars($component->cname); ?></h1>

<table class="versinfotable">
	<tr>
		<th>Project</th><td><a href="<?php echo qs2sef("proj={$component->pkey}");?>"><?php echo $component->pkey;?></a></td>
	</tr>
	<tr>
		<th>Description</th><td><?php echo htmlspecialchars($component->description); ?></td>
	</tr>
	<?php if (!empty($component->URL)):?>
	<tr>
		<th>URL</th><td><a href="<?php echo $component->URL;?>"><?php echo $component->URL;?></a></td>
	</tr>
	<?php endif;?>
	<?php if (!empty($component->LEAD)):?>
	<tr>
		<th>Component Lead</th><td><?php echo $component->LEAD;?></td>
	</tr>
	<?php endif;?>
</table>

<hr />
<!--sphider_noindex-->
<h3>Issues</h3>
<?php include 'issues-table.php'; ?>

<!--/sphider_noindex-->

</body>
</html>

