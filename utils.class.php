<?php
/** JIRA Issue List Script - Utility classes and functions
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



/** Check whether the client IP is in the authorised list
*
* @return boolean
*/
function checkIPs(){

	global $conf;
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

	return $authip;

}




/** Create a SEF style URL
*
* @arg querystring
*
* @return string
*/
function qs2sef($qstring){

	$parts = explode("&",$qstring);
	$sections = array();
	$url = array();
	
	foreach ($parts as $part){
		$v = explode("=",$part);
		$sections[$v[0]] = $v[1];
	}

	// Build the URL
	if (isset($sections['action'])){

	      switch ($sections['action']){

		      case 'sitemap':
			  $url[] = 'sitemap.xml';
		      break;

	      }

	    return "/".implode($url,"/");
	}


	if (isset($sections['proj'])){
		$url[] = 'browse';

		if (isset($sections['issue'])){
			$url[] = $sections['proj']."-".$sections['issue'].".html";
		}elseif(isset($sections['vers'])){
			$url[] = 'versions';
			$url[] = $sections['proj']."-".$sections['vers'].".html";
		}elseif(isset($sections['comp'])){
			$url[] = 'components';
			$url[] = $sections['proj']."-".$sections['comp'].".html";
		}else{
			$url[] = $sections['proj'].".html";
		}
	}elseif(isset($sections['attachment'])){
		// Attachment links
		$url[] = 'browse';
		$url[] = 'attachments';

		if (isset($sections['thumb'])){
			$url[] = 'thumbs';
			$url[] = $sections['attachment'];
			$url[] = $sections['projid']. ":". "_thumb_".$sections['attachment'].".png";
		}else{
			$url[] = $sections['attachment'];
			$url[] = $sections['projid']. ":" . $sections['fname'];
		}
	}else{
		$url[] = 'index.html';
	}

	return "/".implode($url,"/");

}



function parseSEF(){

	$sefurl = explode("?",$_SERVER['REQUEST_URI']); // make sure the query string isn't included (some servers seem to)
	$const = explode("/",ltrim($sefurl[0],"/"));

	if ($const[0] == 'status'){
		$_GET['checkstatus'] = true;
		return;
	}


	if ($const[0] == 'sitemap.xml'){
		$_GET['rendersitemap'] = true;
		$_GET['sitemapbase'] = $_SERVER['HTTP_X_SITEMAP_BASE'];
		return;
	}

	if ($const[0] != 'browse' && $const[0] != 'attachments'){
		return;
	}


		if ($const[1] == 'attachments'){

			if ($const[2] == 'thumbs'){
				$_GET['attachid'] = $const[3];
				$pro = explode("-",$const[4]);
				$_GET['projectID'] = $pro[0];
				$id = explode(":",$pro[1]);
				$_GET['issueid'] = $pro[0]."-".$id[0];

				$_GET['thumbs'] = 1;
				return;
			}


			// It's an attachment link
			$_GET['attachid'] = $const[2];
			$pro = explode(":",$const[3]);

			$_GET['projectID'] = $pro[0];
			$_GET['filename'] = $pro[1];

			return;
		}

		
		// We're checking versions
		if ($const[1] == "versions"){
			$refs = explode("-",str_replace(".html","",$const[2]));
			$_GET['proj'] = $refs[0];
			$_GET['vers'] = $refs[1];
			return;
		}

		// We're checking components
		if ($const[1] == "components"){
			$refs = explode("-",str_replace(".html","",$const[2]));
			$_GET['proj'] = $refs[0];
			$_GET['comp'] = $refs[1];
			return;
		}

		$refs = explode("-",str_replace(".html",'',$const[1]));
		if (isset($refs[1]) && !empty($refs[1])){
			$_GET['issue'] = $refs[1];
		}

		if (!empty($refs[0])){
			$_GET['proj'] = $refs[0];
		}





	return;

}




/** Process some of the jira markup which may be used
*
*/
function jiraMarkup($str,$pkey = false){

	// This one's actually one used by this script
	$str = preg_replace('/\[issuelistpty (.*?)\](.*?)\[\/issuelistpty\]/s','<span class="pty$1">$2</span>',$str);



	// From http://stackoverflow.com/questions/6038061/regular-expression-to-find-urls-within-a-string
	$str = preg_replace('/(http|ftp|https):\/\/([\w\-_]+(?:(?:\.[\w\-_]+)+))([\w\-\.,@?^=%&amp;:\/~\+#]*[\w\-\@?^=%&amp;\/~\+#])?/s',
			    '<a href="$1://$2$3" target=_blank rel="nofollow" class="autolink">$1://$2$3</a>',$str); // Introduced in JILS-25

	// This one's specifically used by my commit notifications
	$str = preg_replace('/(\[View Commit\|)(.*?)(\])/s','<a href="$2" target=\_blank>View Commit</a>',$str);
	$str = preg_replace('/(\[View Changes\|)(.*?)(\])/s','<a href="$2" target=\_blank>View Changes</a>',$str);

        $str = preg_replace('/(\{quote\})(.*?)(\{quote\})/s','<blockquote>$2</blockquote>',$str);
        $str = preg_replace('/(\{noformat\})(.*?)(\{noformat\})/s','<pre>$2</pre>',$str);
        $str = preg_replace('/((?<!\\\)\*)([^\s].*?[^\s])((?<!\\\)\*)/s','<b>$2</b>',$str); // Bolds
        //$str = preg_replace('/((?<!\\\)_)([^\s].*?[^\s])((?<!\\\)_)/s','<i>$2</i>',$str); // Italics - currently causes more trouble than I can be arsed with
	$str = preg_replace('/(\&amp;gt;)/','&gt;',$str);
	$str = preg_replace('/(\&amp;lt;)/','&lt;',$str);

	// Not the best way of doing it, but a quick fix for the time being
	// Strip out the characters MS seem to insist on using
	$str = str_replace(chr(130), ',', $str);    // baseline single quote
	$str = str_replace(chr(132), '"', $str);    // baseline double quote
	$str = str_replace(chr(133), '...', $str);  // ellipsis
	$str = str_replace(chr(145), "'", $str);    // left single quote
	$str = str_replace(chr(146), "'", $str);    // right single quote
	$str = str_replace(chr(147), '"', $str);    // left double quote
	$str = str_replace(chr(148), '"', $str);    // right double quote


      
	// Strip any formatting back out of pre-s
	preg_match_all('/\<pre\>(.*?)\<\/pre\>/s', $str, $match);

	foreach($match as $a){
	    foreach($a as $b){
	     $str = str_replace('<pre>'.$b.'</pre>', "<pre>".str_replace("<i>", "_", str_replace("</i>","_",
		    str_replace("<b>","*",str_replace("</b>","*",$b))))."</pre>", $str); // no, I'm not proud of this, but its getting late and this has been bugging me...
	   	  
	    }
	}
	

	if ($pkey){
		$str = preg_replace("/($pkey\-)([0-9]*)/","<a href='".qs2sef("issue=$2&proj=$pkey")."'>$pkey-$2</a>",$str);
	}

	return $str;
}




/** Taken from the PHP Manual - change newlines to <br /> apart from within pre tags
*
*/
function my_nl2br($string){
$string = str_replace("\n", "<br />", $string);
if(preg_match_all('/\<pre\>(.*?)\<\/pre\>/', $string, $match)){
    foreach($match as $a){
        foreach($a as $b){
        $string = str_replace('<pre>'.$b.'</pre>', "<pre>".str_replace("<br />", "", $b)."</pre>", $string);
        }
    }
}
return $string;
}



/** If the client filter header is set, build an SQL filter based upon it
*
* @arg tblname - Table name/alias to use in the sql statement
* @arg retarry - if true, no SQL will be generated, an array will simply be returned containing the filter values
*
* @return mixed
*/
function buildProjectFilter($tblname=false, $retarray=false){
	$sql = false;
	
	if (isset($_SERVER['HTTP_X_PROJECT_LIMIT']) && !empty($_SERVER['HTTP_X_PROJECT_LIMIT'])){
		// Break down the header
		$keys = explode(",",$_SERVER['HTTP_X_PROJECT_LIMIT']);

		if ($retarray){
		    return $keys; 
		}

		$db = new BTDB();
		$sql = ($tblname)? "$tblname.":'';
		
		$sql .= "pkey IN (";
		$options = '';



		foreach ($keys as $k){
		      $options .= "'".$db->stringEscape($k)."',";
		}
		// Trim the final comma and drop into the SQL statement
		
		$sql .= rtrim($options,",").") ";
	}

	return $sql;
}


/** Apply any relevant IP based filters
*
*/
function apply_filters(){


  global $conf;

  // Limit IP to Projects
  if (array_key_exists("a".$_SERVER['REMOTE_ADDR'],$conf->IPProjectRestrictions)){
      $_SERVER['HTTP_X_PROJECT_LIMIT'] = $conf->IPProjectRestrictions["a".$_SERVER['REMOTE_ADDR']];
  }
  

}

/** Translate a username into a realname
*
* See JILS-14
*
* @arg username
*
* @return string
*/
function translateUser($username){

    global $conf;


    // Are custom overrides enabled, and is one set for this user?
    if ($conf->customUsernames){
	  include 'authors.php';
	  if (isset($authors['a'.$username])){
	      return expandUserRecord($authors['a'.$username]);
	  }
    }


    if ($conf->usernames == 'name'){

	// Check if we've already cached it
	if (isset($GLOBALS['usernamecache']) && isset($GLOBALS['usernamecache']['a'.$username])){
	      return $GLOBALS['usernamecache']['a'.$username];
	}





	$db = new BTDB();
	$sql = "SELECT * FROM cwd_user WHERE user_name='".$db->stringEscape($username)."'";
	$db->setQuery($sql);
	$user = $db->loadResult();

	$user_string = $user->first_name . " " . $user->last_name;
	return $user_string;


    }else{
	return $username;
    }

}


/** Seems a bit overblown having a seperate function for something so simple, but it's likely going to do a lot more in future!
*
*/
function expandUserRecord($user){

      if (isset($user['URL']) && !empty($user['URL'])){
	    return "<a class='extauthorLink' title='View Profile (external site)' href='{$user['URL']}' target=_blank>{$user['DisplayName']}</a>";
      }

      return $user['DisplayName'];
    
}



/** Check the script status - fairly simplistic check for now
*
* See JILS-24
*/
function check_status(){
      $db = new BTDB;
      $db->setQuery('SELECT COUNT(*) AS issuecount from jiraissue');
      $res = $db->loadResult();

      if (!$res){
	  echo "QUERY FAILED";
      }else{
	  echo "QUERY SUCCESS";
      }

}


