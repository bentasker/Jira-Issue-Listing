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
<link rel="alternate" type="application/json" href="index.json">
<title>Available Projects</title>
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
</ul>
<hr />

<p>The following projects are available for browsing</p>

<table class="prjtbl sortable">
  <tr>
    <th>Key</th>
    <th>Title</th>
    <th class="desc">Description</th>
  </tr>

  <?php foreach ($projects as $project): ?>
    <tr>
	<td class="prjkey"><a href="<?php echo qs2sef("proj={$project->pkey}"); ?>"><?php echo $project->pkey;?></a></td>
	<td class="prjname"><?php echo $project->pname; ?></td>
	<td class="desc"><?php echo $project->DESCRIPTION; ?></td>
    </tr>
  <?php endforeach; ?>

</table>

<div style="font-size: 0.5em; ">
  <a href="<?php echo qs2sef('action=sitemap'); ?>">XML Sitemap</a>
  <a href="sitemap.json">JSON Sitemap</a>
  <a href="<?php echo qs2sef('action=movedissues'); ?>" class="nodisplay">Moved Issues</a>
</div>
<!--/sphider_noindex-->
</body>
</html>
