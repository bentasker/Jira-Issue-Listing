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


	
	$sql = "SELECT ID, pname, pkey, DESCRIPTION from project ";

	$filter = buildProjectFilter(); // See JILS-12
	if ($filter){
	    $sql .= "WHERE ".$filter;
	}

	$sql .= ' ORDER BY pkey ASC';

	$db->setQuery($sql);
	$projects = $db->loadResults();

?>
<html>
<head>

<?php require 'head-includes.php'; ?>
</head>
<body class="homepage">
<!--sphider_noindex-->
<hr /><h1>Projects</h1><hr />
<ul itemprop="breadcrumb" class="breadcrumbs">
      <li><a href="index.html">Projects</a></li>
</ul>
<hr />

<table class="prjtbl sortable">
  <tr>
    <th>Key</th>
    <th>Title</th>
    <th class="desc">Description</th>
  </tr>

  <?php foreach ($projects as $project): ?>
    <tr>
	<td><a href="<?php echo qs2sef("proj={$project->pkey}"); ?>"><?php echo $project->pkey;?></a></td>
	<td><?php echo $project->pname; ?></td>
	<td class="desc"><?php echo $project->DESCRIPTION; ?></td>
    </tr>
  <?php endforeach; ?>

</table>

<!--/sphider_noindex-->
</body>
</html>
