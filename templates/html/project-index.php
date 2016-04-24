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
<title><?php echo htmlspecialchars($_GET['proj']); ?></title>

<meta name="description" content="<?php echo htmlentities($project->DESCRIPTION); ?>" />

<?php require 'head-includes.php'; ?>
</head>
<body itemscope itemtype="http://schema.org/CollectionPage">



<!--sphider_noindex-->
<?php
	echo $projdesc;
?>

<?php include 'issues-table.php'; ?>

<br />
<hr />
<br />

<a name="Components"></a>
<h3>Components</h3>

<table class="projectVersionstbl">

<?php foreach ($components as $component):?>
<tr>
        <td><a href="<?php echo qs2sef("comp={$component->ID}&proj={$project->pkey}");?>"><?php echo htmlspecialchars($component->cname); ?></a></td>
	<td><?php echo htmlspecialchars($component->DESCRIPTION); ?></td>
</tr>

<?php endforeach; ?>

</table>


<br />
<hr />
<br />

<a name="versions"></a>
<h3>Versions</h3>

<table class="projectVersionstbl">

<?php foreach ($versions as $version):?>
<tr>
        <td><a href="<?php echo qs2sef("vers={$version->ID}&proj={$project->pkey}");?>"><?php echo htmlspecialchars($version->vname); ?></a></td>
	<td><?php echo htmlspecialchars($version->description); ?></td>
	<td><?php echo ($version->RELEASED)? 'Released' : 'Un-released'; ?> <?php echo ($version->ARCHIVED)? '(Archived)':'';?></td>
	<td><?php echo (!empty($version->RELEASEDATE))? $version->RELEASEDATE : '' ;?></td>
</tr>

<?php endforeach; ?>

</table>

<!--/sphider_noindex-->


<!--URLKEY:/browse/<?php echo htmlspecialchars($_GET['proj']);?>:-->
</body>
</html>


