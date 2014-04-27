<?php
/** JIRA Issue List Script
*
* Simple script to generate an simple HTML listing of JIRA Issues from a Private JIRA Instance
* Intended use is to allow indexing of JIRA issues by internal search engines such as Sphider (http://www.sphider.eu/)
*
* @copyright (C) 2014 B Tasker (http://www.bentasker.co.uk). All rights reserved
* @license GNU GPL V2 - See LICENSE
*
* @version 1.2
*
*/


$conf->db = 'JiraDB';
$conf->host = 'localhost';
$conf->user = 'jiradbuser';
$conf->password = '';
$conf->dbprefix = '';

$conf->scriptname='index.php';
$conf->jiralocation = 'http://jira.example.com';
$conf->SphiderUA = 'Sphider';
$conf->SphiderIP = array('192.168.1.65/32','192.168.1.96'); // You can use CIDR or specify individual IPs


$conf->debug = false; // Enabling this will prevent redirection to JIRA

// Database class begins
class BTDB{

    var $linkident = false;
    var $connreuse = false;
    var $dbname = false;
    var $showDBErrors = true;

    /** Get the basic database config
    *
    * Will adjust to use config file at later date TODO
    *
    */
    function setconfig(){


    global $conf;


    // DB Connection config
    $this->dbname = $conf->db;
    $this->dbserver = $conf->host;
    $this->dbuser = $conf->user;
    $this->dbpass = $conf->password;
    $this->tblprefix = $conf->dbprefix;


    if (isset($conf->showDBErrors)){
      // Display SQL errors?
      $this->showDBErrors = $conf->showDBErrors;
    }

    // To leave connections open after each query
    // set to 1. May have an impact on server performance
    // but will speed up system so long as MySQL server doesn't
    // reach the point of having too many concurrent connections
    $this->connreuse = false;
    }



    /** Replace #__ with the configured tablename prefix
    *
    * @arg sql string
    *
    * @return string
    *
    */
    function setPrefix($sql){

    if (!$this->dbname)
    $this->setconfig(); 

    return str_replace("#__",$this->tblprefix,$sql);
    }


    /** Set the query
    *
    * @arg sql - The MySQL string to run. Data should already be escaped where necessary
    *
    */
    function setQuery($sql){
    $this->query = $this->setPrefix($sql);
    }



    /** Run an insert query and return the Primary Key
    *
    */
    function insertID(){
    if (!$this->linkident)
    $this->opendb(); 

    $res = mysql_query($this->query);

    // Output any errors if configured to do so
    $this->checkErrors();


    $id = mysql_insert_id();
    // if connection re-use has been disabled, close the link
    if (!$this->connreuse){ $this->closedb(); }

    return $id;


    }


    /** Run a query and return the raw MySQL result 
    *
    */
    function runQuery(){

    if (!$this->linkident)
    $this->opendb(); 

    $res = mysql_query($this->query);

    // Output any errors if configured to do so
    $this->checkErrors();

    // if connection re-use has been disabled, close the link
    if (!$this->connreuse){ $this->closedb(); }

    return $res;


    }



    /** Run the query and return a single row
    *
    * @return Result Object
    */
    function loadResult(){

    if (!$this->linkident)
    $this->opendb();

    $additional = "";

    // Enforce 1 row limit if user hasn't done so in query
    if (strpos("LIMIT 1",$this->query) === false)
    $additional = " LIMIT 1";

    $res = mysql_fetch_object(mysql_query($this->query . $additional));

    // Output any errors if configured to do so
    $this->checkErrors();

    // if connection re-use has been disabled, close the link
    if (!$this->connreuse)
    $this->closedb(); 

    return $res;
    }


    /** Run the query and return all results
    *
    * @return Results Object
    */
    function loadResults(){
    if (!$this->linkident)
    $this->opendb();


    $result = mysql_query($this->query);

    $this->checkErrors();


    $X=0;

    while ($row = mysql_fetch_object($result)){
    $rowname = "row$X";
    $results->$rowname = $row;
    $X++;
    }

    // if connection re-use has been disabled, close the link
    if (!$this->connreuse){ $this->closedb(); }

    return $results;

    }


    /** Escape supplied string for safe use in
    * SQL statements
    *
    * @arg string - string to be escaped
    *
    */
    function stringEscape($string){
    if (!$this->linkident)
    $this->opendb();


    $string = mysql_real_escape_string($string);

    if (!$this->connreuse)
    $this->closedb(); 

    return $string;

    }


    /** Escape every value in an array for safe
    * use in SQL statements
    *
    * @arg arr - the array to be escaped
    *
    */
    function arrayEscape($arr){
    if (!$this->linkident)
    $this->opendb();

    array_walk_recursive($arr,'sql_esc');

    if (!$this->connreuse)
    $this->closedb(); 

    return $arr;
    }

    /** Change the supplied variable in place
    *
    */
    function sql_esc(&$var){
    $var = stringEscape($var);
    }



    /** Convert HTML markup to the equivalent markup entity
    *
    */
    function convHTML($string){

    return htmlspecialchars($string);

    }


    /** Check for SQL errors. Output them if configured to do so, but record the status anyway
    *
    */
    function checkErrors(){
    // Output errors if configured to do so
    $error = mysql_error();
    if ($this->showDBErrors){ echo $error; }

    if (!empty($error)){
    $this->errors = $error;
    }
    // We could also implement logging at a later date

    }


    /** Open a Database connection
    *
    */
    function opendb(){

    if (!$this->dbname)
    $this->setconfig(); 


    // Open the Database connection
    $this->linkident = mysql_connect($this->dbserver, $this->dbuser, $this->dbpass);

    if (!$this->linkident) {
	die('Could not connect: ' . mysql_error());
	  }else{

    // Connect to the database if one is named
    if (!empty($this->dbname)){
    $db_selected = mysql_select_db($this->dbname, $this->linkident);
    if (!$db_selected) {
	die ('Can\'t use ' . $this->dbname . ': ' . mysql_error());
	      }

	  }

	}

    }



    /** Close the connection
    *
    */
    function closedb(){

    // Only close the link if it's active
    if (!$this->linkident){ return; }

    mysql_close($this->linkident);
    // Unset the link ID
    $this->linkident=false;

    }


    /** Switch connection re-use on
    *
    */
    function setPersist(){
    $this->connreuse = 1;
    }


    /** Switch connection re-use off
    *
    */
    function unsetPersist(){
    $this->connreuse = 0;
    }

} // End BTDB


/** Calculate all IP's within a subnet based on CIDR - From BT Framework
*
* @arg cidr - The IP Range in CIDR notation (e.g. 192.168.1.0/24)
* @arg inc_broadcast_addr - Should the broadcast address be included in the result?
*
* @return array
*/
function calcIPRange($cidr,$inc_broadcast_addr = true){

	$segs = explode("/",$cidr);
	$bitmask = $segs[1];
	$network = $segs[0];

	if ($bitmask == '32'){
		return array($network);
	}

	$count = pow(2,(32-$bitmask)); // Calculate the number of available IPs
	$startaddr = ip2long($network);
	$ipcount = 1;

	while ($ipcount < $count){
		$ips[] = long2ip($startaddr + $ipcount);
		$ipcount++;
	}


	// Remove the broadcast address if we're not supposed to be including it
	if (!$inc_broadcast_addr){
		$d = array_pop($ips);
	}

	return $ips;

}






$db = new BTDB;

?>
<html>
<head>
<?php

if (!isset($_GET['issue']) || empty($_GET['issue'])):


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
	$db->setQuery($sql);
	$issues = $db->loadResults();

	?>
		</head>
		<body>
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



	$sql = "SELECT a.SUMMARY, a.ID, a.issuenum, a.DESCRIPTION, a.REPORTER, b.pname, b.pkey FROM jiraissue AS a LEFT JOIN project AS b on a.PROJECT = b.ID WHERE a.issuenum='".$db->stringEscape($_GET['issue']) . 
		"' AND b.pkey='".$db->stringEscape($_GET['proj'])."'";

	$db->setQuery($sql);
	$issue = $db->loadResult();


	$sql = "SELECT actionbody, CREATED, AUTHOR FROM jiraaction where actiontype='comment' AND issueid=".(int)$issue->ID." ORDER BY CREATED ASC";
	$db->setQuery($sql);
	$comments = $db->loadResults();

	?>
		<title><?php echo "{$issue->pkey}-{$issue->issuenum}: ".htmlspecialchars($issue->SUMMARY); ?></title>
		<meta name="description" content="<?php echo htmlspecialchars(str_replace('"',"''",$issue->DESCRIPTION)); ?>">

		</head>
		<body>
		<b><a href="<?php echo $conf->scriptname; ?>">BACK</a></b>

		<h1><?php echo "{$issue->pkey}-{$issue->issuenum}"; ?>: <?php echo htmlspecialchars($issue->SUMMARY); ?></h1>

		<table style="border: 0px;">

			<tr><td><br /><br /></td><td></td>
			<tr><td colspan="2"><?php echo $issue->DESCRIPTION; ?><br /><br /></td></tr>
			<tr><td><i>Reported By</i>: <?php echo $issue->REPORTER; ?></td><td><i>Project:</i><?php echo $issue->pname; ?></td></tr>
		</table>

		<h3>Comments</h3>


		<?php foreach ($comments as $comment): ?>	
		<div>
			<b><?php echo $comment->AUTHOR; ?></b><br />
			<i><?php echo $comment->CREATED; ?></i><br /><Br />

			<?php echo $comment->actionbody; ?>
	
		</div>
		<hr />
		<?php endforeach; ?>

<?php
endif;
?>
