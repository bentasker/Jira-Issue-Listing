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


// See whether the client IP is allowed to view us
$projdesc = null;

// Which view are we displaying?

	// Overall listing (all projects, all issues)

	if (!$authip ){
		echo "</head><body>Invalid IP</body></html>";
		die;
	}


	
	$sql = "SELECT ID, pname, pkey, DESCRIPTION from project ORDER BY pkey ASC";
	$db->setQuery($sql);
	$projects = $db->loadResults();

	//$sql = "SELECT a.SUMMARY, a.issuenum, b.pkey FROM jiraissue AS a LEFT JOIN project AS b on a.PROJECT = b.ID ORDER BY a.PROJECT, a.issuenum ASC";

	//$db->setQuery($sql);
	//$issues = $db->loadResults();

?>
<html>
<head>
<style type="text/css">
<?php require 'css.php'; ?>
</style>
<?php require 'head-includes.php'; ?>
</head>
<body class="homepage">
<!--sphider_noindex-->
<h1>Projects</h1>

<table class="prjtbl sortable">
  <tr>
    <th></th>
    <th></th>
    <th class="desc"></th>
  </tr>

  <?php foreach ($projects as $project): ?>
    <tr>
	<td><a href="<?php echo qs2sef("proj={$project->pkey}"); ?>"><?php echo $project->pkey;?></a></td>
	<td><?php echo $project->pname; ?></td>
	<td class="desc"><?php echo $project->DESCRIPTION; ?></td>
    </tr>
  <?php endforeach; ?>

</table>
<?php
/*

	foreach ($issues as $issue){

		echo "<li><a href='".qs2sef("issue={$issue->issuenum}&proj={$issue->pkey}")."'>{$issue->pkey}-{$issue->issuenum}: ".htmlentities(htmlspecialchars($issue->SUMMARY))."</a></li>\n";


	}
*/
?>
<!--/sphider_noindex-->
</body>
</html>
