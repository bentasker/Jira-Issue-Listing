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



require 'config.php';
require 'utils.class.php';

parseSEF();
$db = new BTDB;

if (isset($_GET['attachid'])){

	// No unauthorised access
	if (!$conf->debug && (!in_array($_SERVER['HTTP_USER_AGENT'],$conf->SphiderUA) || !checkIPs())){
		die;
	}
	$inc_ok = true;
	require 'attachment.php';
	die;
}

?>
<html>
<head>
<?php

if (!isset($_GET['issue']) || empty($_GET['issue'])):

	// See whether the client IP is allowed to view us
	$authip = checkIPs();
	$projdesc = null;

	// Which view are we displaying?
	if (!isset($_GET['proj']) || empty($_GET['proj'])){
		// Overall listing (all projects, all issues)

		if (!$authip ){
			echo "</head><body>Invalid IP</body></html>";
			die;
		}

		$sql = "SELECT a.SUMMARY, a.issuenum, b.pkey FROM jiraissue AS a LEFT JOIN project AS b on a.PROJECT = b.ID ORDER BY a.PROJECT, a.issuenum ASC";
		echo "</head></body>";

	}else{


		// Project listing (all issues, one project)

		if (!$conf->debug && (!in_array($_SERVER['HTTP_USER_AGENT'],$conf->SphiderUA) || !$authip)){
			// Redirect real users to JIRA
			header("Location: {$conf->jiralocation}/browse/{$_GET['proj']}");
			die;
		}

		$sql = "SELECT a.SUMMARY, a.issuenum, b.pkey FROM jiraissue AS a LEFT JOIN project AS b on a.PROJECT = b.ID ".
			"WHERE b.pkey='" . $db->stringEscape($_GET['proj']). "' ORDER BY a.PROJECT, a.issuenum ASC";


		// Grab the project information

		
		$db->setQuery("SELECT * FROM project WHERE pkey='" . $db->stringEscape($_GET['proj']). "'");
		$project = $db->loadResult();


		$projdesc = "<h1>".htmlspecialchars($project->pkey).": ".htmlentities(htmlspecialchars($project->pname))."</h1>";

		if (!empty($project->URL)){
			$projdesc .= "<i><a href='{$project->URL}'>{$project->URL}</a></i>\n";
		}	

		$projdesc .= "<h3>Description</h3><pre>".htmlentities(htmlspecialchars($project->DESCRIPTION))."</pre>\n\n<h3>Issues</h3>\n";

		echo "<title>Project: ". htmlspecialchars($_GET['proj']). "</title>\n</head></body>\n".
		 "<!--URLKEY:/browse/" . htmlspecialchars($_GET['proj']) . ":-->\n";

	}

	$db->setQuery($sql);
	$issues = $db->loadResults();

	?>

		<!--sphider_noindex-->

		
	<?php
		echo $projdesc;

	foreach ($issues as $issue){

		echo "<li><a href='".qs2sef("issue={$issue->issuenum}&proj={$issue->pkey}")."'>{$issue->pkey}-{$issue->issuenum}: ".htmlentities(htmlspecialchars($issue->SUMMARY))."</a></li>\n";


	}
	echo "	<!--/sphider_noindex-->";

else:

	if (!$conf->debug && (!in_array($_SERVER['HTTP_USER_AGENT'],$conf->SphiderUA) || !checkIPs())){
		// Redirect real users to JIRA
		header("Location: {$conf->jiralocation}/browse/{$_GET['proj']}-{$_GET['issue']}");
		die;
	}



	$sql = "SELECT a.SUMMARY, a.ID, a.issuenum, a.DESCRIPTION, a.REPORTER, b.pname, b.pkey, c.pname as status, d.pname as resolution, e.pname as issuetype, f.pname as priority,".
		"a.CREATED, a.RESOLUTIONDATE, a.TIMESPENT, f.SEQUENCE as ptysequence ".
		"FROM jiraissue AS a ".
		"LEFT JOIN project AS b on a.PROJECT = b.ID ".
		"LEFT JOIN issuestatus AS c ON a.issuestatus = c.id ".
		"LEFT JOIN resolution AS d ON a.RESOLUTION = d.ID ".
		"LEFT JOIN issuetype AS e ON a.issuetype = e.ID ".
		"LEFT JOIN priority AS f ON a.PRIORITY = f.ID ".
		"WHERE a.issuenum='".$db->stringEscape($_GET['issue']) . 
		"' AND b.pkey='".$db->stringEscape($_GET['proj'])."'";

	$db->setQuery($sql);
	$issue = $db->loadResult();

	if (!$issue){
	    header("HTTP/1.0 404 Not Found");
	    echo "ISSUE NOT FOUND";
	    die;
	}


	// Get Previous Issue
	$sql = "SELECT a.issuenum, a.SUMMARY, b.pkey FROM jiraissue as a ".
	      "LEFT JOIN project as b on a.PROJECT = b.ID ".
	      "WHERE a.CREATED < '".$db->stringEscape($issue->CREATED)."' ".
	      " AND b.pkey='".$db->stringEscape($_GET['proj'])."'".
	      "ORDER BY a.CREATED DESC";
	$db->setQuery($sql);
	$previssue = $db->loadResult();


	// Get Next Issue
	$sql = "SELECT a.issuenum, a.SUMMARY, b.pkey FROM jiraissue as a ".
	      "LEFT JOIN project as b on a.PROJECT = b.ID ".
	      "WHERE a.CREATED > '".$db->stringEscape($issue->CREATED)."' ".
	      " AND b.pkey='".$db->stringEscape($_GET['proj'])."'".
	      "ORDER BY a.CREATED ASC";
	$db->setQuery($sql);
	$nextissue = $db->loadResult();



	$sql = "SELECT ID, actionbody, CREATED, AUTHOR FROM jiraaction where actiontype='comment' AND issueid=".(int)$issue->ID." ORDER BY CREATED ASC";
	$db->setQuery($sql);
	$comments = $db->loadResults();



	// Get Relations
	$sql = "select a.*,b.* from issuelink as a LEFT JOIN issuelinktype as b ON a.LINKTYPE=b.ID WHERE a.SOURCE=".(int)$issue->ID . " OR a.DESTINATION=".(int)$issue->ID;
	$db->setQuery($sql);
	$relations = $db->loadResults();


	// Get Attachments
	$sql = "select * from fileattachment where issueid=".(int)$issue->ID." ORDER BY CREATED ASC";
	$db->setQuery($sql);
	$attachments = $db->loadResults();

	// Get Workflow (exclude time estimate and timespent - we'll deal with those later)
	$sql = "SELECT a.CREATED, a.AUTHOR, b.* FROM `changegroup` AS a LEFT JOIN `changeitem` AS b on a.ID = b.groupid WHERE a.issueid=".(int)$issue->ID.
	" AND b.FIELD IN ('status','resolution','assignee','Fix Version','Version','labels','Attachment','priority,'timespent','Project','Key')".
	" AND a.CREATED > '".$db->stringEscape($issue->CREATED)."' ORDER BY a.created ASC";
	$db->setQuery($sql);
	$workflow = $db->loadResults();


	// Merge the workflow with comments, first bit's easy
	$commentsmerged = array();

	foreach ($comments as $comment){
	    $t = strtotime($comment->CREATED);
	    $comment->rowtype = 'comment';
	    
	    // Don't overwrite a previous comment if two people commented at the same time. Increment the array key by one until we find a free one
	    while (true){
		$k = "a".$t;
		if (!isset($commentsmerged[$k])){
		    $commentsmerged[$k] = $comment;
		    break;
		}
		$t++;
	    }

	}

	// Now we need to process the workflow and turn it into comments
	foreach ($workflow as $wf){
	    $t = strtotime($wf->CREATED);
	    $co = new stdClass();
	    $co->ID = 'wf'.$wf->ID;
	    $co->AUTHOR = '';
	    $co->CREATED = $wf->CREATED;

	    if (!empty($wf->NEWSTRING)){

		  // Tweak the value depending on field type
		  switch ($wf->FIELD){
			case 'timespent':
			      $wf->NEWSTRING = ($wf->NEWSTRING / 60) . " minutes";
			      $wf->OLDSTRING = ($wf->OLDSTRING / 60) . " minutes";
			break;

			case 'Attachment':
			      $wf->FIELD = 'Attachments';
			break;

			case 'priority':
			      $sql = "SELECT SEQUENCE FROM priority WHERE ID=".(int)$wf->NEWVALUE;
			      $db->setQuery($sql);
			      $s = $db->loadResult();

			      $wf->NEWSTRING = '[issuelistpty '. $s->SEQUENCE .']'.$wf->NEWSTRING.'[/issuelistpty]';

			      if (!empty($wf->OLDSTRING)){
				  $sql = "SELECT SEQUENCE FROM priority WHERE ID=".(int)$wf->OLDVALUE;
				  $db->setQuery($sql);
				  $s = $db->loadResult();

				  $wf->OLDSTRING = '[issuelistpty '. $s->SEQUENCE .']'.$wf->OLDSTRING.'[/issuelistpty]';
			      }
			break;

		  }


		  if (!empty($wf->OLDSTRING)){
		    $co->actionbody = "{$wf->AUTHOR} changed {$wf->FIELD} from '{$wf->OLDSTRING}' to '{$wf->NEWSTRING}'";
		  }else{
		    $co->actionbody = "{$wf->AUTHOR} added '{$wf->NEWSTRING}' to {$wf->FIELD}";
		  }

	    }else{
		  $co->actionbody = "{$wf->AUTHOR} removed '{$wf->OLDSTRING}' from {$wf->FIELD}";
	    }

	    $co->rowtype = 'statechange';

	    while (true){
	      $k= "a".$t;
	      if (!isset($commentsmerged[$k])){
		  $commentsmerged[$k] = $co;
		  break;
	      } 
	      $t++;
	    }
	}

	// Sort by timestamp
	ksort($commentsmerged);

	// Get Versions
	$sql = "SELECT b.vname FROM nodeassociation AS a ".
		"LEFT JOIN projectversion AS b on a.SINK_NODE_ID = b.ID ".
		"WHERE a.SOURCE_NODE_ID=".(int)$issue->ID." AND a.ASSOCIATION_TYPE='IssueVersion'";
	$db->setQuery($sql);
	$affectsversions = $db->loadResults();


	// Get Components
	$sql = "SELECT a.SOURCE_NODE_ID, b.cname FROM nodeassociation AS a ".
		"LEFT JOIN component AS b on a.SINK_NODE_ID = b.ID ".
		"WHERE a.SOURCE_NODE_ID=".(int)$issue->ID." AND a.ASSOCIATION_TYPE='IssueComponent'";
	$db->setQuery($sql);
	$components = $db->loadResults();


	// Get Target Fix version
	$sql = "SELECT b.vname FROM nodeassociation AS a ".
	"LEFT JOIN projectversion AS b on a.SINK_NODE_ID = b.ID ".
	"WHERE a.SOURCE_NODE_ID=".(int)$issue->ID." AND a.ASSOCIATION_TYPE='IssueFixVersion'";
	$db->setQuery($sql);
	$fixversions = $db->loadResults();


	$resolution = (empty($issue->resolution))? 'Unresolved' : $issue->resolution. " ({$issue->RESOLUTIONDATE})";

	?>
		<title><?php echo "{$issue->pkey}-{$issue->issuenum}: ".htmlentities(htmlspecialchars($issue->SUMMARY)); ?></title>
		<meta name="description" content="<?php echo htmlentities(htmlspecialchars(str_replace('"',"''",$issue->DESCRIPTION))); ?>">

		<style type="text/css">
				blockquote{border-left: 1px solid black;
				padding-left: 10px;
				background: lightgray;
				padding-bottom: 10px;
				word-wrap: break-word;
				}


				.commentlink {font-size: 0.7em; float: right;}
				.statechangetext {font-style: italic;}
				.commenttext, .issuedescription {font-family: monospace}
				table.issueInfo {width: 100%; border: 0px;}
				.reporter {font-style: italic; }

				.statusOpen {color: red}
				.statusClosed {color: green}

				.pty4, .pty5 {color: green;}
				.pty3 {color: red; }
				.pty1, .pty2 {color: red; font-weight: bolder; }
				.prevlink {float:left;}
				.nextlink {float:right;}
				.prevlink a {text-decoration: none;}
				.nextlink a {text-decoration: none;}

		</style>

		</head>
		<body>

		<!--sphider_noindex-->
		  <?php if($previssue): ?>
		    <span class="prevlink">
			<a href="<?php echo qs2sef("issue={$previssue->issuenum}&proj={$previssue->pkey}"); ?>">
				&lt; <?php echo $previssue->pkey."-".$previssue->issuenum;?>: <?php echo htmlentities(htmlspecialchars($previssue->SUMMARY)); ?>
			</a>
		    </span>
		  <?php endif;?>

		  <?php if($nextissue): ?>
		    <span class="nextlink">
		      <a href="<?php echo qs2sef("issue={$nextissue->issuenum}&proj={$nextissue->pkey}"); ?>">
				 <?php echo $nextissue->pkey."-".$nextissue->issuenum;?>: <?php echo htmlentities(htmlspecialchars($nextissue->SUMMARY)); ?> &gt;
		      </a>
		    </span>
		  <?php endif; ?>
		  <div style="clear: both; width: 100%;"></div>
		<!--/sphider_noindex-->

		<hr />
		<a name="top"></a><h1><?php echo "{$issue->pkey}-{$issue->issuenum}"; ?>: <?php echo htmlentities(htmlspecialchars($issue->SUMMARY)); ?></h1>
		<hr />
		<a name="Info"></a>
			<h3>Issue Information</h3>
			<table class="issueInfo">
			<tr><td><b>Issue Type</b>: <?php echo $issue->issuetype; ?></td><td>&nbsp;</td></tr>
			<tr><td><b>Priority</b>: <span class="pty<?php echo $issue->ptysequence;?>"><?php echo $issue->priority; ?></span></td><td><b>Status</b>: <span class="status<?php echo $issue->status;?>"><?php echo $issue->status;?></span></b></td></tr>
                        <tr><td><br /></td><td></td></tr>

			<tr><td><b>Reported By</b>: <span class="reporter"><?php echo $issue->REPORTER; ?></span></td><td><b>Resolution:</b> <?php echo $resolution; ?></td></tr>
			<tr><td><b>Project:</b> <?php echo $issue->pname; ?> (<a href="<?php echo qs2sef("proj={$issue->pkey}");?>"><?php echo $issue->pkey; ?></a>)</td>
				<td>&nbsp;</td></tr>

			<tr><td><b>Affects Version: </b><span class="issueversions"><?php foreach ($affectsversions as $af):
								      echo htmlentities(htmlspecialchars($af->vname)). ", " ;
								  endforeach;
								?></span>
							    </td>
			    <td><b>Target version: </b><span class="issueversions"><?php foreach ($fixversions as $af):
								      echo htmlentities(htmlspecialchars($af->vname)). ", " ;
								  endforeach;
								?></span>
			    </td></tr>
			<tr><td><b>Components: </b><span class="issuecomponents"><?php foreach ($components as $af):
								      echo htmlentities(htmlspecialchars($af->cname)). ", " ;
								  endforeach;
								?></span></td><td>&nbsp;</td></tr>
			<tr><td><br /></td><td></td></tr>
			<!--sphider_noindex-->
				<tr><td><b>Created</b>: <?php echo $issue->CREATED; ?></td><td><b>Time Spent Working</b>: <?php echo $issue->TIMESPENT / 60; ?> minutes</td></tr>
				<tr><td><br /><br /></td><td></td>
			<!--/sphider_noindex-->

			<tr><td colspan="2"><b>Description</b><br /><div class="issuedescription"><?php echo nl2br(jiraMarkup(htmlentities(htmlspecialchars($issue->DESCRIPTION)),$issue->pkey)); ?></div><br /><br /></td></tr>

		</table>


	<?php if (count($attachments) > 0):?>
		<a name="Attachments"></a><div style="border: 1px solid #000; padding: 10px; margin-top: 40px;">
			<h4>Attachments</h4>
			<table style="width: 40%">
				<tr><td>&nbsp</td></tr>
				<?php foreach ($attachments as $attachment): ?>
					<?php $alink = qs2sef("attachment={$attachment->ID}&fname={$attachment->FILENAME}&projid={$issue->pkey}-{$issue->issuenum}"); ?>
					<tr>
						<td>
							<a href="<?php echo $alink; ?>">
							<?php if (!$attachment->thumbnailable):?>
								<?php echo htmlspecialchars($attachment->FILENAME);?>
							<?php else: ?>
								<img style="margin: 5px;" src="<?php echo qs2sef("attachment={$attachment->ID}&fname={$attachment->FILENAME}&projid={$issue->pkey}-{$issue->issuenum}&thumb=1"); ?>" title="<?php echo htmlspecialchars($attachment->FILENAME);?>">
							<?php endif;?>
							</a>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>

		</div>

	<?php endif;?>


	<?php if (count($relations) > 0):
		$subtasks=array();

		?>
		<!--sphider_noindex-->
		<a name="Links"></a><div style="border: 1px solid #000; padding: 10px; margin-top: 40px;">
				<h4>Issue Links</h4>
				<table>

					<?php foreach ($relations as $relation):


							$remid = ($relation->DESTINATION == $issue->ID)? $relation->SOURCE : $relation->DESTINATION;
							$reltype = ($relation->DESTINATION == $issue->ID)? $relation->INWARD : $relation->OUTWARD;

							if ($reltype == "jira_subtask_outward"){
								$subtasks[] = $relation;
								continue;
							}elseif($reltype == "jira_subtask_inward"){
								$reltype="Parent Issue: ";
							}

							$sql = "SELECT a.SUMMARY, a.issuenum, b.pkey, d.pname as resolution FROM jiraissue AS a ".
								"LEFT JOIN project AS b on a.PROJECT = b.ID ".
								"LEFT JOIN resolution AS d ON a.RESOLUTION = d.ID " .
								"WHERE a.id=".(int)$remid;
							$db->setQuery($sql);
							$relatedissue = $db->loadResult();
							$resolved = (empty($resolution))? false : true;
						?>
						<tr>
							<td>
								<?php echo htmlentities(htmlspecialchars($reltype)); ?>
							</td>

							<td>
								<?php if ($resolved):?><del><?php endif; ?>
								<a href='<?php echo qs2sef("issue={$relatedissue->issuenum}&proj={$relatedissue->pkey}"); ?>'>
									<?php echo "{$relatedissue->pkey}-{$relatedissue->issuenum}</a>: ";?>
								<?php if ($resolved):?></del><?php endif; ?>

									<?php echo htmlentities(htmlspecialchars($relatedissue->SUMMARY)); ?>
							
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
		</div>


		<?php if (count($subtasks) > 0): ?>
			<a name="subtasks"></a><div style="border: 1px solid #000; padding: 10px; margin-top: 40px;">
					<h4>Subtasks</h4>
					<table>

						<?php foreach ($subtasks as $relation):


								$remid = ($relation->DESTINATION == $issue->ID)? $relation->SOURCE : $relation->DESTINATION;
								$reltype = ($relation->DESTINATION == $issue->ID)? $relation->INWARD : $relation->OUTWARD;

								$sql = "SELECT a.SUMMARY, a.issuenum, b.pkey, d.pname as resolution FROM jiraissue AS a ".
									"LEFT JOIN project AS b on a.PROJECT = b.ID ".
									"LEFT JOIN resolution AS d ON a.RESOLUTION = d.ID " .
									"WHERE a.id=".(int)$remid;
								$db->setQuery($sql);
								$relatedissue = $db->loadResult();
								$resolved = (empty($resolution))? false : true;
							?>
							<tr>

								<td>
									<?php if ($resolved):?><del><?php endif; ?>
									<a href='<?php echo qs2sef("issue={$relatedissue->issuenum}&proj={$relatedissue->pkey}"); ?>'>
										<?php echo "{$relatedissue->pkey}-{$relatedissue->issuenum}</a>: ";?>
									<?php if ($resolved):?></del><?php endif; ?>

										<?php echo htmlentities(htmlspecialchars($relatedissue->SUMMARY)); ?>
							
								</td>
							</tr>
						<?php endforeach; ?>
					</table>
			</div>
		<?php endif; ?>


		<!--/sphider_noindex-->
	<?php endif; ?>


		<div style="border: 1px solid #000; padding: 10px; margin-top: 40px;">
		<a name="comments"></a><h4>Comments</h4>
			
			<hr />

			<?php foreach ($commentsmerged as $comment): ?>	
			<div><a name="comment<?php echo $comment->ID;?>"></a>
				<b><?php echo $comment->AUTHOR; ?></b>    <a class="commentlink" href="#comment<?php echo $comment->ID;?>" rel="nofollow">Permalink</a><br />
				<i><?php echo $comment->CREATED; ?></i><br /><Br />

				
				<div class="<?php echo $comment->rowtype;?>text"><?php echo nl2br(jiraMarkup(htmlentities(htmlspecialchars($comment->actionbody)),$issue->pkey)); ?></div>
				
	
			</div>
			<hr />
			<?php endforeach; ?>

		</div>


<!--URLKEY:/browse/<?php echo "{$issue->pkey}-{$issue->issuenum}";?>:-->
<?php
endif;
?>
</body>
</html>
