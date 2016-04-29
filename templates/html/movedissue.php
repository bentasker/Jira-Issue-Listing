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


if (!$conf->debug && (!in_array($_SERVER['HTTP_USER_AGENT'],$conf->SphiderUA) || !checkIPs())){
	// Redirect real users to JIRA
	header("Location: {$conf->jiralocation}/browse/{$_GET['proj']}-{$_GET['issue']}");
	die;
}

if (!$issue){
    header("x-robots-tag: no-index, nofollow");
    echo "INVALID - HOW DID YOU GET HERE?";


}

/***          HTML BEGINS    */
?>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Moved issue: <?php echo "{$issue->OLD_ISSUE_KEY}"; ?></title>
	<meta name="robots" content="noindex" />
	<?php require 'head-includes.php'; ?>
	</head>
	<body itemscope itemtype="http://schema.org/WebPage">



	<!--sphider_noindex-->
	<a name="top"></a>
		<div id='logoarea'></div>
		<h1 itemprop="name">Moved issue: <?php echo "{$issue->pkey}-{$issue->issuenum}"; ?></h1>
	<hr />

	<ul itemprop="breadcrumb" class="breadcrumbs">
	      <li><a href="../index.html">Projects</a></li>
	      <li><a href="<?php echo qs2sef("action=movedissues");?>">Moved Issues</a></li>
	      <li><a href="<?php echo qs2sef("issue={$issue->issuenum}&proj={$issue->pkey}");?>"><?php echo "{$issue->pkey}-{$issue->issuenum}"; ?></a></li>
	</ul>
	<hr />



	<a name="Info"></a>
	<h3>Issue Information</h3>

	<div class="issueInfo">
	    This issue (<?php echo "{$issue->OLD_ISSUE_KEY}"; ?>) has moved to <a href="<?php echo qs2sef("issue={$issue->issuenum}&proj={$issue->pkey}");?>"><?php echo $issue->pkey."-".$issue->issuenum; ?></a>


	    <script type="text/javascript">
		window.location.href = '<?php echo qs2sef("issue={$issue->issuenum}&proj={$issue->pkey}",".html",false);?>';
	    </script>

	</div>
</body>
</html>
