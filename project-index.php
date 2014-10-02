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


// Project listing (all issues, one project)

if (!$conf->debug && (!in_array($_SERVER['HTTP_USER_AGENT'],$conf->SphiderUA) || !$authip)){
	// Redirect real users to JIRA
	header("Location: {$conf->jiralocation}/browse/{$_GET['proj']}");
	die;
}


// Grab the project information


$db->setQuery("SELECT * FROM project WHERE pkey='" . $db->stringEscape($_GET['proj']). "'");
$project = $db->loadResult();

// Get details of timespent
$db->setQuery("SELECT SUM(TIMESPENT) as TIMESPENT, SUM(TIMEORIGINALESTIMATE) as estimate FROM jiraissue where PROJECT=".(int)$project->ID);
$time = $db->loadResult();

$timespent = ($time->TIMESPENT /60);
if ($timespent > 60){
    // Convert to hours
    $timespent = round(($timespent / 60),2) . " hours";
}else{
    $timespent .= " minutes";
}

$timeestimate = ($time->estimate / 60);
if ($timeestimate > 60){
    // Convert to hours
    $timeestimate = round(($timeestimate / 60),2) . " hours";
}else{
    $timeestimate .= " minutes";
}


// Get Versions

$sql = "select * from projectversion WHERE PROJECT=".(int)$project->ID;
$db->setQuery($sql);
$versions = $db->loadResults();



$projdesc = "<h1>".htmlspecialchars($project->pkey).": ".htmlentities(htmlspecialchars($project->pname))."</h1>";

if (!empty($project->URL)){
	$projdesc .= "<i><a href='{$project->URL}'>{$project->URL}</a></i>\n";
}	

$projdesc .= "<h3>Description</h3>".$project->DESCRIPTION."<br /><br />".
	      "\n<i>Initial Estimate: </i> $timeestimate<br />\n".
	      "<i>Time Logged: </i>$timespent<br />".
	      "\n\n<h3>Issues</h3>\n";

echo "<title>Project: ". htmlspecialchars($_GET['proj']). "</title>\n</head></body>\n".
 "<!--URLKEY:/browse/" . htmlspecialchars($_GET['proj']) . ":-->\n";


$sql = "SELECT a.SUMMARY, a.ID, a.issuenum, a.REPORTER, b.pname, b.pkey, c.pname as status, d.pname as resolution, e.pname as issuetype, f.pname as priority,".
	"a.CREATED, a.RESOLUTIONDATE, a.TIMESPENT, f.SEQUENCE as ptysequence ".
	"FROM jiraissue AS a ".
	"LEFT JOIN project AS b on a.PROJECT = b.ID ".
	"LEFT JOIN issuestatus AS c ON a.issuestatus = c.id ".
	"LEFT JOIN resolution AS d ON a.RESOLUTION = d.ID ".
	"LEFT JOIN issuetype AS e ON a.issuetype = e.ID ".
	"LEFT JOIN priority AS f ON a.PRIORITY = f.ID ".
	"WHERE b.pkey='".$db->stringEscape($_GET['proj'])."'" . 
	" ORDER BY a.PROJECT, a.issuenum ASC" ;

$db->setQuery($sql);
$issues = $db->loadResults();





?>
<html>
<head>
<title><?php echo htmlspecialchars($_GET['proj']); ?></title>
<style type="text/css">
<?php require 'css.php'; ?>
</style>
<?php require 'head-includes.php'; ?>
</head>
<body>
<!--sphider_noindex-->
<?php
	echo $projdesc;

?>
<table class="issuelistingtable sortable">
<tr>
	<th>Key</th><th>Type</th><th>Pty</th><th>Summary</th><th>Status</th><th>Resolution</th><th>Created</th>
</tr>

<?php foreach ($issues as $issue):?>

	<tr>
           <td><a href='<?php echo qs2sef("issue={$issue->issuenum}&proj={$issue->pkey}");?>'><?php echo "{$issue->pkey}-{$issue->issuenum}"; ?></a></td>
	   <td><?php echo $issue->issuetype; ?></td>
	   <td><span class="pty<?php echo $issue->ptysequence;?>"><?php echo $issue->priority; ?></span></td>
	   <td><?php echo htmlspecialchars($issue->SUMMARY); ?></td>
	   <td><span class="status<?php echo $issue->status;?>"><?php echo $issue->status; ?></span></td>
	   <td><?php echo $issue->resolution; ?></td>
	   <td sorttable_custom_key="<?php echo strtotime($issue->CREATED); ?>"><?php echo $issue->CREATED; ?></td>
	</tr>

<?php endforeach; ?>
</table>

<br />
<hr />
<br />

<a name="versions"></a>
<h3>Versions</h3>

<table class="projectVersionstbl">

<?php foreach ($versions as $version):?>
<tr>
	<td><?php echo htmlspecialchars($version->vname); ?></td>
	<td><?php echo htmlspecialchars($version->DESCRIPTION); ?></td>
	<td><?php echo ($version->RELEASED)? 'Released' : 'Un-released'; ?></td>
	<td><?php echo (!empty($version->RELEASEDATE))? $version->RELEASEDATE : '' ;?></td>
</tr>

<?php endforeach; ?>

</table>

<!--/sphider_noindex-->


<!--URLKEY:/browse/<?php echo htmlspecialchars($_GET['proj']);?>:-->
</body>
</html>


