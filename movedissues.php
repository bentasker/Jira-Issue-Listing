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


$sql = "SELECT a.OLD_ISSUE_KEY, b.pname, b.pkey, ji.issuenum FROM `moved_issue_key` AS a ".
"LEFT JOIN jiraissue AS ji ON a.ISSUE_ID = ji.ID ".
"LEFT JOIN project AS b on ji.PROJECT = b.ID ";

$filter = buildProjectFilter('b'); // See JILS-12
if ($filter){
    $sql .= " WHERE ".$filter;
}

$db->setQuery($sql);
$issues = $db->loadResults();



?>
<html>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Moved Issues</title>
<?php require 'head-includes.php'; ?>
</head>
<body class="homepage">
<!--sphider_noindex-->
<hr />
	<div id='logoarea'></div>
	<h1>Projects</h1>
<hr />


<ul itemprop="breadcrumb" class="breadcrumbs">
      <li><a href="index.html">Projects</a></li>
      <li><a href="<?php echo qs2sef("action=movedissues");?>">Moved Issues</a></li>
</ul>
<hr />

<p>The following issues have moved to a new location</p>

<ul>
<?php foreach ($issues as $issue):?>

  <li><a href="browse/<?php echo $issue->OLD_ISSUE_KEY; ?>.html"><?php echo $issue->OLD_ISSUE_KEY; ?></a> -&gt; <a href="<?php echo qs2sef("issue={$issue->issuenum}&proj={$issue->pkey}");?>"><?php echo $issue->pkey."-".$issue->issuenum; ?></a></li>

<?php endforeach; ?>
</body>

</html>
