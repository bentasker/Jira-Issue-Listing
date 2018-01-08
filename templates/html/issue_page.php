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
	<title><?php echo "{$issue->pkey}-{$issue->issuenum}: ".htmlentities($issue->SUMMARY); ?></title>
	<link rel="alternate" type="application/json" href="<?php echo qs2sef("issue={$issue->issuenum}&proj={$issue->pkey}",".json");?>">
	<link rel="alternate" type="text/plain" href="<?php echo qs2sef("issue={$issue->issuenum}&proj={$issue->pkey}",".txt");?>">
	<meta name="description" content="<?php echo htmlentities(str_replace('"',"''",$issue->DESCRIPTION)); ?>">

	<?php if (count($labels) > 0 ):?>
	    <meta name="keywords" content="<?php foreach ($labels as $label){ echo "{$label->LABEL},"; }?>" />
	<?php endif;?>

	<script type="text/javascript">
		  function toggleStatusActivities(){
		    var entries = document.getElementsByClassName('activitystatechange');
		    for (i=0; i<entries.length;i++){
			if (entries[i].style.display != 'none'){
			    entries[i].style.display = 'none';
			}else{
			    entries[i].style.display = 'block';
			}
		    }
		  }
	</script>
	<?php require 'head-includes.php'; ?>
	</head>
	<body itemscope itemtype="http://schema.org/WebPage">

	<meta itemprop="commentCount" content="<?php echo $comment_count; ?>" />

	<!--sphider_noindex-->
	  <?php if($previssue): ?>
	    <span itemscope itemtype="http://schema.org/SiteNavigationElement" class="prevlink">
		<a itemprop="url" href="<?php echo qs2sef("issue={$previssue->issuenum}&proj={$previssue->pkey}"); ?>">
			&lt; <?php echo $previssue->pkey."-".$previssue->issuenum;?>: <?php echo htmlspecialchars($previssue->SUMMARY); ?>
		</a>
	    </span>
	  <?php endif;?>

	  <?php if($nextissue): ?>
	    <span itemscope itemtype="http://schema.org/SiteNavigationElement" class="nextlink">
	      <a itemprop="url" href="<?php echo qs2sef("issue={$nextissue->issuenum}&proj={$nextissue->pkey}"); ?>">
			 <?php echo $nextissue->pkey."-".$nextissue->issuenum;?>: <?php echo htmlspecialchars($nextissue->SUMMARY); ?> &gt;
	      </a>
	    </span>
	  <?php endif; ?>
	  <div style="clear: both; width: 100%;"></div>
	<!--/sphider_noindex-->

	<hr />
	<a name="top"></a>
		<div id='logoarea'></div>
		<h1 itemprop="name"><?php echo "{$issue->pkey}-{$issue->issuenum}"; ?>: <?php echo htmlentities($issue->SUMMARY); ?></h1>
	<hr />

	<ul itemprop="breadcrumb" class="breadcrumbs">
	      <li><a href="../index.html">Projects</a></li>
	      <li><a href="<?php echo qs2sef("proj={$issue->pkey}");?>"><?php echo $issue->pkey; ?></a></li>

	      <?php if ($parent): ?>
		    <li><a href="<?php echo qs2sef("issue={$parent->issuenum}&proj={$parent->pkey}"); ?>"><?php echo "{$parent->pkey}-{$parent->issuenum}";?></a></li>
	      <?php endif; ?>


	      <li><a href="<?php echo qs2sef("issue={$issue->issuenum}&proj={$issue->pkey}");?>"><?php echo "{$issue->pkey}-{$issue->issuenum}"; ?></a></li>
	</ul>
	<hr />



	<a name="Info"></a>
	<h3>Issue Information</h3>

	<div class="issueInfo">
		<div class="row">
			<div class="leftcol"><b>Issue Type</b>: <?php echo $issue->issuetype; ?></div>
			<div class="rightcol">&nbsp;</div>
		</div>

		<div class="row">
			<div class="leftcol" id="isspty"><b>Priority</b>: <span class="pty<?php echo $issue->ptysequence;?>"><?php echo $issue->priority; ?></span></div>
			<div class="rightcol" id="issstatus"><b>Status</b>: <span class="status<?php echo $issue->status;?>"><?php echo $issue->status;?></span></b></div>
		</div>

		<div class="row">
                	<div class="leftcol"><br /></div>
			<div class="rightcol"></div>
		</div>

		<div class="row">
		    <div class="leftcol" id="issrptr"><b>Reported By:</b> <span itemprop="contributor" class="reporter"><?php echo translateUser($issue->REPORTER); ?></span></div>
		    <div class="rightcol" id="issassignee"><b>Assigned To:</b> <span itemprop="contributor" class="assignee"><?php echo translateUser($issue->ASSIGNEE); ?></span></div>
		</div>
		<div class="mobilespacer">
		</div>

		<div class="row">
		    <div class="leftcol" id="issproject">
			<b>Project:</b> <?php echo $issue->pname; ?> (<a itemprop="isPartOf" href="<?php echo qs2sef("proj={$issue->pkey}");?>"><?php echo $issue->pkey; ?></a>)
		    </div>
		    <div class="rightcol" id="issresolution"><b>Resolution:</b> <?php echo $resolution; ?></div>
		</div>

		<div class="mobilespacer">
		</div>

		<div class="row">
		    <div class="leftcol" id="issaffectsver">
			<?php if (count($affectsversions) > 0):?>
			  <b>Affects Version: </b><span class="issueversions">
							<?php foreach ($affectsversions as $af):?>
							      <a href="<?php echo qs2sef("vers={$af->ID}&proj={$issue->pkey}"); ?>"><?php echo htmlspecialchars($af->vname);?></a>,  
							<?php endforeach;?>
						  </span>
			<?php endif; ?>
		    </div>
		    <div class="rightcol" id="issfixver">
			<?php if (count($fixversions) > 0): ?>
			  <b>Target version: </b><span class="issueversions">
							<?php foreach ($fixversions as $af):?>
							      <a itemprop="version" href="<?php echo qs2sef("vers={$af->ID}&proj={$issue->pkey}"); ?>"><?php echo htmlspecialchars($af->vname);?></a>,
							  <?php endforeach;?>
						  </span>
			<?php endif; ?>
		    </div>
		</div>

		<div class="row">
		    <div class="leftcol" id="isscomponents">
			<?php if (count($components) > 0): ?>
			  <b>Components: </b><span class="issuecomponents">
						<?php foreach ($components as $af):?>
							      <a href="<?php echo qs2sef("comp={$af->ID}&proj={$issue->pkey}"); ?>"><?php echo htmlspecialchars($af->cname);?></a> , 
						<?php endforeach;?>
					    </span>
			<?php endif; ?>
		    </div>
		    <div class="rightcol" id="isslabels">
		      <?php if (count($labels) > 0 ):?>
			  <b>Labels: </b><span itemprop="keywords"><?php foreach ($labels as $label){ echo "{$label->LABEL}, "; }?></span>
		      <?php endif;?>
		    </div>
		</div>

		<?php if (!empty($issue->ENVIRONMENT)):?>
		<div class="row" id="issenvironment">
			<div class="leftcol"><b>Environment:</b> <?php echo nl2br(jiraMarkup(htmlentities($issue->ENVIRONMENT),$issue->pkey)); ?></div>
			<div class="rightcol"></div>
		</div>
		<?php endif; ?>

		<div class="row">
			<div class="leftcol"><br /></div>
			<div class="rightcol"></div>
		</div>
		<!--sphider_noindex-->
		<div class="row">
			<div class="leftcol" id="isscreated"><b>Created</b>: <?php echo $issue->CREATED; ?></div>
			<div class="rightcol" id="isstimelogged">
			    <b>Time Spent Working</b><br >
			    <div id='tmwrksbtsks'>
				  <?php echo createTimeBar($TIMESPENT,$TIMEESTIMATE,$TIMEORIGINALESTIMATE,true) ;?>
			    </div>
			    <div id='tmwrk' style="display: none;">
				  <?php echo createTimeBar($issue->TIMESPENT,$issue->TIMEESTIMATE,$issue->TIMEORIGINALESTIMATE,true) ;?>
			    </div>
			    <input type="checkbox" onchange="toggleTimeWork(this)" id="tmwrktoggle" checked><label for="tmwrktoggle">Include Subtasks</label>

			</div>
		</div>

	      <?php if ($parent): ?>
			  <div class="row">
				  <div class="leftcol">
				      <b>Child of:</b> <a href='<?php echo qs2sef("issue={$parent->issuenum}&proj={$parent->pkey}"); ?>'>
					    <?php echo "{$parent->pkey}-{$parent->issuenum}</a>: ";?> <?php echo htmlentities($parent->SUMMARY); ?>
				  </div>
				  <div class="rightcol"></div>
			  </div>
	      <?php endif; ?>

		<div class="row">
			<div class="leftcol"><br /><br /></div>
			<div class="rightcol"></div>
		</div>
		<!--/sphider_noindex-->



		<div class="row">
			<div class="colspan2" id="issdescription">
				<b>Description</b><br /><div class="issuedescription"><?php echo my_nl2br(jiraMarkup(htmlentities($issue->DESCRIPTION),$issue->pkey)); ?></div><br /><br />
			</div>
		</div>
	</div>
	<div style="clear: both"></div>

<?php if (count($attachments) > 0):?>
	<a name="Attachments"></a><div id="attachmentsblock">
		<h4>Attachments</h4>
		<table>
			<?php foreach ($attachments as $attachment): ?>
				<?php $alink = qs2sef("attachment={$attachment->ID}&fname={$attachment->FILENAME}&projid={$issue->pkey}-{$issue->issuenum}"); ?>
				<tr>
					<td>
						<a target=_blank href="<?php echo $alink; ?>">
						<?php if (!$attachment->thumbnailable):?>
							<?php echo htmlentities($attachment->FILENAME);?>
						<?php else: ?>
							<span itemscope itemtype="http://schema.org/ImageObject">
								<meta itemprop="caption" value="<?php echo str_replace('"',"'",htmlentities($attachment->FILENAME));?>" />
								<meta itemprop="representativeOfPage" value="False" />
								
								<img itemprop="thumbnail" 
								src="<?php echo qs2sef("attachment={$attachment->ID}&fname={$attachment->FILENAME}&projid={$issue->pkey}-{$issue->issuenum}&thumb=1"); ?>" 
								title="<?php echo htmlspecialchars($attachment->FILENAME);?>">
							</span>
						<?php endif;?>
						</a>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>

	</div>

<?php endif;?>


<?php if (count($relations) > 0 || count($relationsext) > 0):
	
	?>
	<!--sphider_noindex-->
	<a name="Links"></a><div id="linksblock">
			<h4>Issue Links</h4>
			<table>

				<?php foreach ($relations as $relation):
						$remid = ($relation->DESTINATION == $issue->ID)? $relation->SOURCE : $relation->DESTINATION;
						$reltype = ($relation->DESTINATION == $issue->ID)? $relation->INWARD : $relation->OUTWARD;
						$relatedissue = $relation->relatedissue;
						$resolved = $relation->resolved;
					?>
					<tr class='issue<?php echo str_replace(" ","_",htmlspecialchars($reltype)); ?> related_is_resolved_<?php echo $resolved; ?>'>
						<td>
							<?php echo htmlspecialchars($reltype); ?>
						</td>

						<td>
							<?php if ($resolved):?><del><?php endif; ?>
							<a href='<?php echo qs2sef("issue={$relatedissue->issuenum}&proj={$relatedissue->pkey}"); ?>'>
								<?php echo "{$relatedissue->pkey}-{$relatedissue->issuenum}</a>: ";?>
							<?php if ($resolved):?></del><?php endif; ?>

								<?php echo htmlentities($relatedissue->SUMMARY); ?>
						
						</td>
					</tr>
				<?php endforeach; ?>
				<?php foreach ($relationsext as $relation):?>
				    <tr>
					    <td><?php if (!empty($relation->ICONURL)):?><img class='favicon' src="<?php echo $relation->ICONURL; ?>"><?php endif; ?></td>
					    <td><a target=_blank href="<?php echo $relation->URL; ?>"><?php echo $relation->TITLE;?></a></td>
				    </tr>
				<?php endforeach; ?>
			</table>
	</div>
	<!--/sphider_noindex-->
<?php endif; ?>


<?php if (count($subtasks) > 0): ?>
	<!--sphider_noindex-->
		<a name="subtasks"></a><div id="subtasksblock">
				<h4>Subtasks</h4>
				<table>

					<?php foreach ($subtasks as $relation):


							$remid = ($relation->DESTINATION == $issue->ID)? $relation->SOURCE : $relation->DESTINATION;
							$reltype = ($relation->DESTINATION == $issue->ID)? $relation->INWARD : $relation->OUTWARD;
							$relatedissue = $relation->relatedissue;
							$resolved = $relation->resolved;
						?>
						<tr>

							<td>
								<?php if ($resolved):?><del><?php endif; ?>
								<a href='<?php echo qs2sef("issue={$relatedissue->issuenum}&proj={$relatedissue->pkey}"); ?>'>
									<?php echo "{$relatedissue->pkey}-{$relatedissue->issuenum}</a>: ";?>
								<?php if ($resolved):?></del><?php endif; ?>
							</td>
							<td>

									<?php echo htmlentities($relatedissue->SUMMARY); ?>						
							</td>
							<td>
									<?php echo createTimeBar($relatedissue->TIMESPENT,$relatedissue->TIMEESTIMATE,$relatedissue->TIMEORIGINALESTIMATE,false); ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
		</div>



	<!--/sphider_noindex-->
<?php endif; ?>


	<div id="commentsblock">
	<a style="float:right; font-size: 0.8em; text-decoration: none;" onclick="toggleStatusActivities(); return false;" href="#">Toggle State Changes</a>
	<a name="comments"></a><h4>Activity</h4>
		
		<hr />

		<?php foreach ($commentsmerged as $comment): ?>	
		<div class="activityentry activity<?php echo $comment->rowtype;?> comment_author_<?php echo $comment->AUTHOR; ?>"
		    <?php if ($comment->rowtype == 'comment'): ?>
			itemscope itemtype="http://schema.org/Comment"
		    <?php else: ?>
			itemscope itemtype="http://schema.org/StateChange"
		    <?php endif; ?>
		    >
		      <a name="comment<?php echo $comment->ID;?>"></a>
		      <div class="commentMetadata">
			<b itemprop="author"><?php echo translateUser($comment->AUTHOR); ?></b>    <a itemprop="url" class="commentlink" href="#comment<?php echo $comment->ID;?>" rel="nofollow">Permalink</a><br />
			<i itemprop="dateCreated"><?php echo $comment->CREATED; ?></i>
		      </div>
			
			<div class="<?php echo $comment->rowtype;?>text" itemprop="text"><?php echo my_nl2br(jiraMarkup(htmlentities($comment->actionbody),$issue->pkey)); ?></div>
			

		</div>
		
		<?php endforeach; ?>

		<div id='addComment' class='jsAddition'></div>

	</div>



<?php if (count($worklog) > 0): ?>
  <!--sphider_noindex-->
      <div id="worklogblock">
	<a name="worklog"></a><h4>Work log</h4>
		
		<hr />

		<?php foreach ($worklog as $work): ?>	
		<div><a name="worklog<?php echo $work->ID;?> worklog_author_<?php echo $work->AUTHOR;?>"></a>
			<b><?php echo translateUser($work->AUTHOR); ?></b>    <a class="commentlink" href="#worklog<?php echo $work->ID;?>" rel="nofollow">Permalink</a><br />
			<i><?php echo $work->CREATED; ?></i><br /><Br />

			<span class='worklogindex'>Time Spent: </span><span class="timespent"><?php echo ($work->timeworked / 60) . " minutes"; ?></span>
			<div class="worklogtext">
			      <span class='worklogindex'>Log Entry: </span><?php echo nl2br(jiraMarkup(htmlentities($work->worklogbody),$issue->pkey)); ?>
			</div>
			

		</div>
		<hr />
		<?php endforeach; ?>

	</div>
  <!--/sphider_noindex-->
<?php endif; ?>

<?php foreach ($authors_list as $author): ?>
  <meta itemprop="contributor" content="<?php echo $author; ?>" />
<?php endforeach; ?>

<!--URLKEY:/browse/<?php echo "{$issue->pkey}-{$issue->issuenum}";?>:-->

</body>
</html>
