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


?>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Version: <?php echo htmlspecialchars($version->vname); ?></title>
<link rel="alternate" type="application/json" href="<?php echo qs2sef("vers={$version->ID}&proj={$version->pkey}",".json");?>">

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
<?php include 'templates/html/issues-table.php'; ?>

<?php if (count($outstanding_issues) > 0):?>
	<a name='buglist'></a><h3>Known Issues</h3>
	<?php $tblid = 'biscuits';
	$issues = $outstanding_issues;
	include 'templates/html/issues-table.php'; ?>
<?php endif; ?>

<!--/sphider_noindex-->

</body>
</html>

