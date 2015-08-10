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
$tblid=mt_rand();
?>

<div id='addIssueTop' class='jsAddition'></div>

<table class="issuelistingtable sortable" id="issuelistingtbl<?php echo $tblid;?>">
<tr>
	<th>Key</th><th>Type</th><th>Pty</th><th>Summary</th><th>Status</th><th>Resolution</th><th>Created</th><th>Assigned To</th>
</tr>

<?php foreach ($issues as $issue):?>

	<tr itemscope itemtype="http://schema.org/WebPage">
           <td class='issKey'><a itemprop="url name" href='<?php echo qs2sef("issue={$issue->issuenum}&proj={$issue->pkey}");?>'><?php echo "{$issue->pkey}-{$issue->issuenum}"; ?></a></td>
	   <td class='issType type<?php echo $issue->issuetype; ?>'><?php echo $issue->issuetype; ?></td>
	   <td class='issPty'><span class="pty<?php echo $issue->ptysequence;?>"><?php echo $issue->priority; ?></span></td>
	   <td class='issSummary' itemprop="description"><?php echo htmlspecialchars($issue->SUMMARY); ?></td>
	   <td class='issStatus'><span class="status<?php echo $issue->status;?>"><?php echo $issue->status; ?></span></td>
	   <td class='issRes'><?php echo $issue->resolution; ?></td>
	   <td class='issCreated' sorttable_custom_key="<?php echo strtotime($issue->CREATED); ?>" itemprop="dateCreated"><?php echo $issue->CREATED; ?></td>
	   <td class='issAssignee' itemprop="contributor"><?php echo translateUser($issue->ASSIGNEE); ?></td>
	</tr>

<?php endforeach; ?>
</table>

<div id='addIssueBottom' class='jsAddition'></div>
<script type="text/javascript">
      var tf = new TableFilter('issuelistingtbl<?php echo $tblid;?>', {
          base_path: '/static/tablefilter/',
          auto_filter: "true",
          col_1: "select",
          col_2: "select",
          col_4: "select",
          col_5: "select",
          col_7: "select",
          paging: true,
          paging_length: 20 
      });

      tf.init();
</script>

