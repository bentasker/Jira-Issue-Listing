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
<title><?php echo $component->pkey;?> / <?php echo htmlspecialchars($component->cname); ?></title>
<link rel="alternate" type="application/json" href="<?php echo qs2sef("comp={$component->ID}&proj={$component->pkey}",".json");?>">
<?php require 'head-includes.php'; ?>
</head>
<body>

<hr /><h1>		
	<div id='logoarea'></div>
	<a href="<?php echo qs2sef("proj={$component->pkey}");?>"><?php echo $component->pkey;?></a> / <?php echo htmlspecialchars($component->cname); ?></h1><hr />
	<ul itemprop="breadcrumb" class="breadcrumbs">
	      <li><a href="../../index.html">Projects</a></li>
	      <li><a href="<?php echo qs2sef("proj={$component->pkey}");?>"><?php echo $component->pkey; ?></a></li>
	      <li><a href="<?php echo qs2sef("comp={$component->ID}&proj={$component->pkey}");?>"><?php echo htmlspecialchars($component->cname); ?></a></li>
	</ul>

	<hr />

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

