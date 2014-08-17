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

$db = new BTDB;

?>
<html>
<head>
<?php

if (!isset($_GET['issue']) || empty($_GET['issue'])):


	// Which view are we displaying?
	if (!isset($_GET['proj']) || empty($_GET['proj'])){
		// Overall listing (all projects, all issues)
		$authip = false;

		foreach ($conf->SphiderIP as $ip){
			if (strpos($ip,"/") === false){

				if ($ip == $_SERVER['REMOTE_ADDR']){
					$authip = true;
					break;
				}

			}else{
				$range = calcIPRange($ip,false);

				if (in_array($_SERVER['REMOTE_ADDR'],$range)){
					$authip = true;
					break;
				}

			}

		}

		if (!$authip ){
			echo "</head><body>Invalid IP</body></html>";
			die;
		}

		$sql = "SELECT a.SUMMARY, a.issuenum, b.pkey FROM jiraissue AS a LEFT JOIN project AS b on a.PROJECT = b.ID ORDER BY a.PROJECT, a.issuenum ASC";
		echo "</head></body>";

	}else{


		// Project listing (all issues, one project)

		if (!$conf->debug && $_SERVER['HTTP_USER_AGENT'] != $conf->SphiderUA){
			// Redirect real users to JIRA
			header("Location: {$conf->jiralocation}/browse/{$_GET['proj']}");
			die;
		}

		$sql = "SELECT a.SUMMARY, a.issuenum, b.pkey FROM jiraissue AS a LEFT JOIN project AS b on a.PROJECT = b.ID ".
			"WHERE b.pkey='" . $db->stringEscape($_GET['proj']). "' ORDER BY a.PROJECT, a.issuenum ASC";

		echo "<title>Project: ". htmlspecialchars($_GET['proj']). "</title>\n</head></body>\n<h1>Project ".htmlspecialchars($_GET['proj'])."</h1>\n".
		 "<!--URLKEY:/browse/" . htmlspecialchars($_GET['proj']) . "-->\n";

	}

	$db->setQuery($sql);
	$issues = $db->loadResults();

	?>

		<!--sphider_noindex-->
	<?php

	foreach ($issues as $issue){

		echo "<li><a href='{$conf->scriptname}?issue={$issue->issuenum}&proj={$issue->pkey}'>{$issue->pkey}-{$issue->issuenum}: ".htmlspecialchars($issue->SUMMARY)."</a></li>\n";


	}
	echo "	<!--/sphider_noindex-->";
else:

	if (!$conf->debug && $_SERVER['HTTP_USER_AGENT'] != $conf->SphiderUA){
		// Redirect real users to JIRA
		header("Location: {$conf->jiralocation}/browse/{$_GET['proj']}-{$_GET['issue']}");
		die;
	}



	$sql = "SELECT a.SUMMARY, a.ID, a.issuenum, a.DESCRIPTION, a.REPORTER, b.pname, b.pkey, c.pname as status, d.pname as resolution, e.pname as issuetype, f.pname as priority,".
		"a.CREATED, a.RESOLUTIONDATE ".
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
		<title><?php echo "{$issue->pkey}-{$issue->issuenum}: ".htmlspecialchars($issue->SUMMARY); ?></title>
		<meta name="description" content="<?php echo htmlspecialchars(str_replace('"',"''",$issue->DESCRIPTION)); ?>">

		</head>
		<body>
		<b><a href="<?php echo $conf->scriptname; ?>">BACK</a></b>

		<h1><?php echo "{$issue->pkey}-{$issue->issuenum}"; ?>: <?php echo htmlspecialchars($issue->SUMMARY); ?></h1>

		<table style="border: 0px;">


			<tr><td><b>Issue Type</b>: <?php echo $issue->issuetype; ?></td><td>&nbsp;</td></tr>
			<tr><td><b>Priority</b>: <?php echo $issue->priority; ?></td><td>&nbsp;</td></tr>
			<tr><td><b>Reported By</b>: <?php echo $issue->REPORTER; ?></td><td><b>Status</b>: <?php echo $issue->status;?></b></td></tr>
			<tr><td><b>Project:</b><?php echo $issue->pname; ?> (<a href="<?php echo "{$conf->scriptname}?proj={$issue->pkey}";?>"><?php echo $issue->pkey; ?></a>)</td>
				<td><b>Resolution:</b> <?php echo $resolution; ?></td></tr>

			<tr><td><br /><br /></td><td></td>

			<tr><td><b>Created</b>: <?php echo $issue->CREATED; ?></td><td>&nbsp;</td></tr>
			<tr><td><br /><br /></td><td></td>

			<tr><td colspan="2"><b>Description</b><br /><pre><?php echo htmlspecialchars($issue->DESCRIPTION); ?></pre><br /><br /></td></tr>

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
								<?php echo htmlspecialchars($reltype); ?>
							</td>

							<td>
								<?php if ($resolved):?><del><?php endif; ?>
								<a href='<?php echo $conf->scriptname ."?issue={$relatedissue->issuenum}&proj={$relatedissue->pkey}"; ?>'>
									<?php echo "{$relatedissue->pkey}-{$relatedissue->issuenum}</a>: ";?>
								<?php if ($resolved):?></del><?php endif; ?>

									<?php echo htmlspecialchars($relatedissue->SUMMARY); ?>
							
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

				
				<pre><?php echo htmlspecialchars($comment->actionbody); ?></pre>
				
	
			</div>
			<hr />
			<?php endforeach; ?>

		</div>






<!--URLKEY:/browse/<?php echo "{$issue->pkey}-{$issue->pkey}";?>-->
<?php
endif;
?>
</body>
</html>
