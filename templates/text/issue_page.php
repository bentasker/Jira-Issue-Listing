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
header("Content-Type: text/plain");
?>

Issue: <?php echo $issue->pkey; ?>-<?php echo $issue->issuenum; ?>

Issue Type: <?php echo $issue->issuetype; ?> 
------------------------------------------------------------------------------------------------------------

Issue Information
====================

Priority: <?php echo $issue->priority; ?>					Status:      <?php echo $issue->status;?>

Resolution:  <?php echo $resolution; ?>

Project: <?php echo $issue->pname; ?> (<?php echo $issue->pkey; ?>)


Reported By: <?php echo $issue->REPORTER ?>					
Assigned To: <?php echo $issue->ASSIGNEE; ?>

<?php if ($parent): ?>
Child of: <?php echo "{$parent->pkey}-{$parent->issuenum} - {$parent->SUMMARY} ";?> 

<?php endif; ?>
<?php if (!empty($issue->ENVIRONMENT)):?>
Environment:<?php echo $issue->ENVIRONMENT; ?>

<?php endif; ?>

<?php if (count($components) > 0): ?>
Components: 
<?php foreach ($components as $af):?>
	- <?php echo $af->cname;?> 
<?php endforeach;?>
<?php endif; ?>

<?php if (count($affectsversions) > 0):?>
Affected Versions:		
<?php foreach ($affectsversions as $af):?>
	- <?php echo $af->vname;?>  			
<?php endforeach;?>

<?php endif; ?>

<?php if (count($fixversions) > 0): ?>
Targeted for fix in version: 
<?php foreach ($fixversions as $af):?>
	- <?php echo $af->vname;?>
<?php endforeach;?>

<?php endif; ?>

<?php if (count($labels) > 0 ):?>
Labels: <?php foreach ($labels as $label){ echo "{$label->LABEL}, "; }?>

<?php endif;?>

Time Estimate: <?php echo $issue->TIMEESTIMATE/60; ?> minutes
Time Logged:   <?php echo $issue->TIMESPENT/60; ?> minutes


------------------------------------------------------------------------------------------------------------

Issue Description
==================

<?php echo textProcessMarkup($issue->DESCRIPTION); ?>


<?php if (count($attachments) > 0):?>
------------------------------------------------------------------------------------------------------------

Attachments
============

<?php foreach ($attachments as $attachment): ?>
	- <?php echo $attachment->FILENAME;?>

<?php endforeach; ?>


<?php endif;?>
<?php if (count($relations) > 0 || count($relationsext) > 0): ?>
------------------------------------------------------------------------------------------------------------

Issue Relations
================
	
<?php foreach ($relations as $relation):
		$remid = ($relation->DESTINATION == $issue->ID)? $relation->SOURCE : $relation->DESTINATION;
		$reltype = ($relation->DESTINATION == $issue->ID)? $relation->INWARD : $relation->OUTWARD;
		$relatedissue = $relation->relatedissue;
		$resolved = $relation->resolved;
	?>
	- <?php echo $reltype; ?> <?php echo "{$relatedissue->pkey}-{$relatedissue->issuenum}: {$relatedissue->SUMMARY}";?>

<?php endforeach; ?><?php foreach ($relationsext as $relation):?>
	- <?php echo $relation->TITLE;?> (<?php echo $relation->URL; ?>)

<?php endforeach; ?>


<?php endif; ?>
<?php if (count($subtasks) > 0): ?>
------------------------------------------------------------------------------------------------------------

Subtasks
==========

<?php foreach ($subtasks as $relation):
		$remid = ($relation->DESTINATION == $issue->ID)? $relation->SOURCE : $relation->DESTINATION;
		$reltype = ($relation->DESTINATION == $issue->ID)? $relation->INWARD : $relation->OUTWARD;
		$relatedissue = $relation->relatedissue;
		$resolved = $relation->resolved;
	?>
	- <?php echo "{$relatedissue->pkey}-{$relatedissue->issuenum}: {$relatedissue->SUMMARY}";?>

<?php endforeach; ?>


<?php endif; ?>
------------------------------------------------------------------------------------------------------------

Activity
==========

<?php foreach ($commentsmerged as $comment): ?>	
-------------------------------------------------
<?php echo $comment->CREATED; ?>              <?php if ($comment->rowtype == 'comment'): ?><?php echo $comment->AUTHOR;?><?php endif; ?>

-------------------------------------------------

<?php echo textProcessMarkup($comment->actionbody); ?>

<?php endforeach; ?>


<?php if (count($worklog) > 0): ?>
------------------------------------------------------------------------------------------------------------

Worklog
========

<?php foreach ($worklog as $work): ?> 
-------------------------------------------------
<?php echo $work->CREATED; ?>              <?php echo $work->AUTHOR;?>


<?php echo ($work->timeworked / 60) . " minutes"; ?>

-------------------------------------------------

<?php echo textProcessMarkup($work->worklogbody); ?>

<?php endforeach ; ?>
<?php endif; ?>


