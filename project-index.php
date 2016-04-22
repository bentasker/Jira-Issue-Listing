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

$sql = "SELECT * FROM project WHERE pkey='" . $db->stringEscape($_GET['proj']). "'";

$filter = buildProjectFilter(); // See JILS-12
if ($filter){
    $sql .= " AND ".$filter;
}

$db->setQuery($sql);
$project = $db->loadResult();


if (!$project){
    header("HTTP/1.0 404 Not Found",true,404);
    echo "PROJECT NOT FOUND";
    die;
}

// Introduced in JILS-41
$sql = "SELECT MAX(cg.CREATED) as lastupdate ".
	"FROM jiraissue AS a ".
	"LEFT JOIN project AS b on a.PROJECT = b.ID ".
	"LEFT JOIN changegroup AS cg ON a.ID = cg.issueid " .
	"LEFT JOIN changeitem AS ci ON cg.ID = ci.groupid " .
	"WHERE b.pkey='".$db->stringEscape($_GET['proj'])."'";
	
$db->setQuery($sql);
$lastchange = $db->loadResult();
$lchange=strtotime($lastchange->lastupdate);
$dstring=gmdate('D, d M Y H:i:s T',$lchange);

// Take changes to the project record itself into account. Could do with being able to do that with Last-Mod, but haven't seen a way yet
$etag="prj-".$_GET['proj']."-".sha1("lc:$lchange;v:".json_encode($project));

header("Last-Modified: $dstring");
header("ETag: $etag");

// Introduced in JILS-41
evaluateConditionalRequest($dstring,$etag);

if (stripos($_SERVER['REQUEST_METHOD'], 'HEAD') !== FALSE) {
       	exit();
}



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

$sql = "select * from projectversion WHERE PROJECT=".(int)$project->ID. " ORDER BY SEQUENCE ASC";
$db->setQuery($sql);
$versions = $db->loadResults();


// Get Components
$sql = "select * from component WHERE PROJECT=".(int)$project->ID . " ORDER BY cname ASC";
$db->setQuery($sql);
$components = $db->loadResults();

/** TODO: Sort this mess out! */
$projdesc = "<hr /><div id='logoarea'></div><h1 itemprop='name'>".htmlspecialchars($project->pkey).": ".htmlentities(htmlspecialchars($project->pname))."</h1><hr />";

$projdesc .= '<ul itemprop="breadcrumb" class="breadcrumbs">
	      <li><a href="../index.html">Projects</a></li>
	      <li><a href="'.qs2sef("proj={$project->pkey}").'">'.$project->pkey.'</a></li>
	    </ul>
	    <hr />';

if (!empty($project->URL)){
	$projdesc .= "<i><a itemprop='relatedLink' href='{$project->URL}'>{$project->URL}</a></i>\n";
}	

if (!empty($project->DESCRIPTION)){
  $projdesc .= "<div class='projdesc'>".$project->DESCRIPTION."</div>";
}

if ($timeestimate > 0 || $timespent > 0){
	$projdesc .= "\n<i>Initial Estimate: </i> $timeestimate<br />\n".
		      "<i>Time Logged: </i>$timespent<br />";
}

$projdesc .= "\n\n<h3>Issues</h3>\n";


$sql = "SELECT a.SUMMARY, a.ID, a.issuenum, a.REPORTER, b.pname, b.pkey, c.pname as status, d.pname as resolution, e.pname as issuetype, f.pname as priority,".
	"a.CREATED, a.RESOLUTIONDATE, a.TIMESPENT, f.SEQUENCE as ptysequence, a.ASSIGNEE ".
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
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo htmlspecialchars($_GET['proj']); ?></title>

<meta name="description" content="<?php echo htmlentities($project->DESCRIPTION); ?>" />

<?php require 'head-includes.php'; ?>
</head>
<body itemscope itemtype="http://schema.org/CollectionPage">



<!--sphider_noindex-->
<?php
	echo $projdesc;
?>

<?php include 'issues-table.php'; ?>

<br />
<hr />
<br />

<a name="Components"></a>
<h3>Components</h3>

<table class="projectVersionstbl">

<?php foreach ($components as $component):?>
<tr>
        <td><a href="<?php echo qs2sef("comp={$component->ID}&proj={$project->pkey}");?>"><?php echo htmlspecialchars($component->cname); ?></a></td>
	<td><?php echo htmlspecialchars($component->DESCRIPTION); ?></td>
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
        <td><a href="<?php echo qs2sef("vers={$version->ID}&proj={$project->pkey}");?>"><?php echo htmlspecialchars($version->vname); ?></a></td>
	<td><?php echo htmlspecialchars($version->description); ?></td>
	<td><?php echo ($version->RELEASED)? 'Released' : 'Un-released'; ?> <?php echo ($version->ARCHIVED)? '(Archived)':'';?></td>
	<td><?php echo (!empty($version->RELEASEDATE))? $version->RELEASEDATE : '' ;?></td>
</tr>

<?php endforeach; ?>

</table>

<!--/sphider_noindex-->


<!--URLKEY:/browse/<?php echo htmlspecialchars($_GET['proj']);?>:-->
</body>
</html>


