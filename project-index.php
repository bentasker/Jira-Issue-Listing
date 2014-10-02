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

$sql = "SELECT a.SUMMARY, a.issuenum, b.pkey FROM jiraissue AS a LEFT JOIN project AS b on a.PROJECT = b.ID ".
	"WHERE b.pkey='" . $db->stringEscape($_GET['proj']). "' ORDER BY a.PROJECT, a.issuenum ASC";


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

$db->setQuery($sql);
$issues = $db->loadResults();


?>
<html>
<head>
<title><?php echo htmlspecialchars($_GET['proj']); ?></title>
</head>
<body>
<!--sphider_noindex-->
<?php
	echo $projdesc;

foreach ($issues as $issue){

	echo "<li><a href='".qs2sef("issue={$issue->issuenum}&proj={$issue->pkey}")."'>{$issue->pkey}-{$issue->issuenum}: ".htmlentities(htmlspecialchars($issue->SUMMARY))."</a></li>\n";

}
?>
<!--/sphider_noindex-->


<!--URLKEY:/browse/<?php echo htmlspecialchars($_GET['proj']);?>:-->
</body>
</html>


