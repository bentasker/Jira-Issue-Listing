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
