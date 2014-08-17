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
		"a.CREATED, a.RESOLUTIONDATE, a.TIMESPENT ".
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


	$sql = "SELECT actionbody, CREATED, AUTHOR FROM jiraaction where actiontype='comment' AND issueid=".(int)$issue->ID." ORDER BY CREATED ASC";
	$db->setQuery($sql);
	$comments = $db->loadResults();



	// Get Relations
	$sql = "select a.*,b.* from issuelink as a LEFT JOIN issuelinktype as b ON a.LINKTYPE=b.ID WHERE a.SOURCE=".(int)$issue->ID . " OR a.DESTINATION=".(int)$issue->ID;
	$db->setQuery($sql);
	$relations = $db->loadResults();


	$resolution = (empty($issue->resolution))? 'Unresolved' : $issue->resolution. " ({$issue->RESOLUTIONDATE})";

	?>
		<title><?php echo "{$issue->pkey}-{$issue->issuenum}: ".htmlentities(htmlspecialchars($issue->SUMMARY)); ?></title>
		<meta name="description" content="<?php echo htmlentities(htmlspecialchars(str_replace('"',"''",$issue->DESCRIPTION))); ?>">

		</head>
		<body>

		<h1><?php echo "{$issue->pkey}-{$issue->issuenum}"; ?>: <?php echo htmlentities(htmlspecialchars($issue->SUMMARY)); ?></h1>

		<table style="border: 0px;">


			<tr><td><b>Issue Type</b>: <?php echo $issue->issuetype; ?></td><td>&nbsp;</td></tr>
			<tr><td><b>Priority</b>: <?php echo $issue->priority; ?></td><td>&nbsp;</td></tr>
			<tr><td><b>Reported By</b>: <?php echo $issue->REPORTER; ?></td><td><b>Status</b>: <?php echo $issue->status;?></b></td></tr>
			<tr><td><b>Project:</b><?php echo $issue->pname; ?> (<a href="<?php echo qs2sef("proj={$issue->pkey}");?>"><?php echo $issue->pkey; ?></a>)</td>
				<td><b>Resolution:</b> <?php echo $resolution; ?></td></tr>

			<tr><td><br /><br /></td><td></td>

			<!--sphider_noindex-->
				<tr><td><b>Created</b>: <?php echo $issue->CREATED; ?></td><td><b>Time Spent Working</b>: <?php echo $issue->TIMESPENT / 60; ?> minutes</td></tr>
				<tr><td><br /><br /></td><td></td>
			<!--/sphider_noindex-->

			<tr><td colspan="2"><b>Description</b><br /><pre><?php echo htmlentities(htmlspecialchars($issue->DESCRIPTION)); ?></pre><br /><br /></td></tr>

		</table>


	<?php if (count($relations) > 0):?>
		<!--sphider_noindex-->
		<div style="border: 1px solid #000; padding: 10px; margin-top: 40px;">
				<h4>Issue Links</h4>
				<table>

					<?php foreach ($relations as $relation):


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
		<!--/sphider_noindex-->
	<?php endif; ?>


		<div style="border: 1px solid #000; padding: 10px; margin-top: 40px;">
		<h4>Comments</h4>
			
			<hr />

			<?php foreach ($comments as $comment): ?>	
			<div>
				<b><?php echo $comment->AUTHOR; ?></b><br />
				<i><?php echo $comment->CREATED; ?></i><br /><Br />

				
				<pre><?php echo htmlentities(htmlspecialchars($comment->actionbody)); ?></pre>
				
	
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
