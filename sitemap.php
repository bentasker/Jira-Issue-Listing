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
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"> 
<?php
/** Project Entries */

$sql = "SELECT ID, pname, pkey, DESCRIPTION from project ";

$filter = buildProjectFilter(); // See JILS-12
if ($filter){
    $sql .= "WHERE ".$filter;
}

$sql .= ' ORDER BY pkey ASC';

$db->setQuery($sql);
$projects = $db->loadResults();


/** Project Issues */
$sql = "SELECT a.SUMMARY, a.issuenum, b.pkey, a.RESOLUTIONDATE FROM jiraissue AS a LEFT JOIN project AS b on a.PROJECT = b.ID ";
$filter = buildProjectFilter('b');
if ($filter){
    $sql .= "WHERE ".$filter;
}

$sql .= ' ORDER BY a.PROJECT, a.issuenum ASC';

$db->setQuery($sql);
$issues = $db->loadResults();



foreach ($projects as $project):?>
  <url>
    <loc><?php echo $_GET['sitemapbase'] . qs2sef("proj={$project->pkey}"); ?></loc>
    <changefreq>weekly</changefreq>
    <priority>0.5</priority>
  </url>
<?php endforeach;

foreach ($issues as $issue):?>
  <url>
    <loc><?php echo $_GET['sitemapbase'] . qs2sef("issue={$issue->issuenum}&proj={$issue->pkey}"); ?></loc>
    <changefreq><?php if (is_null($issue->RESOLUTIONDATE)):?>daily<?php else: ?>yearly<?php endif;?></changefreq>
    <priority>0.5</priority>
  </url>
<?php endforeach; ?>
</urlset>

