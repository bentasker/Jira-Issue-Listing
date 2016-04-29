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
	$to_check = $_SERVER['REMOTE_ADDR'];

	// Introduced for JILS-37
	if (count($conf->AuthorisedProxies) > 0 && in_array($to_check,$conf->AuthorisedProxies) && isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
		// Connection came from an authorised proxy. Use X-Forwarded-For
		$entries = explode(",",$_SERVER['HTTP_X_FORWARDED_FOR']);
		$to_check = $entries[0];
		unset($entries);
	}


	foreach ($conf->SphiderIP as $ip){
		if (strpos($ip,"/") === false){

			if ($ip == $to_check){
				$authip = true;
				break;
			}

		}else{
			$range = calcIPRange($ip,false);

			if (in_array($to_check,$range)){
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
* @arg prepend - should the path be included
*
* @return string
*/
function qs2sef($qstring,$prepend=true){

	$parts = explode("&",$qstring);
	$sections = array();
	$url = array();
	$leadslash = "/";
	
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
		      case 'movedissues':
			  $url[] = 'movedissues.html';
			  break;

		      break;

	      }

	    return $leadslash.implode($url,"/");
	}


	if (isset($sections['proj'])){
		$url[] = 'browse';
		$kanban = (isset($sections['kanban']) && $sections['kanban'] == 1)? "-kanban" : "";

		if (isset($sections['issue'])){
			$url[] = $sections['proj']."-".$sections['issue'].".html";
		}elseif(isset($sections['vers'])){
			$url[] = 'versions';
			$url[] = $sections['proj']."-".$sections['vers'].$kanban.".html";
		}elseif(isset($sections['comp'])){
			$url[] = 'components';
			$url[] = $sections['proj']."-".$sections['comp'].".html";
		}else{
			$url[] = $sections['proj'].$kanban.".html";
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

	if (!$prepend){
	    $url = array(end($url));
	    $leadslash = "";
	}

	return $leadslash.implode($url,"/");

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

	if ($const[0] == 'movedissues.html'){
		$_GET['rendermovedissues'] = true;
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
			$_GET['kanban'] = ($refs[3] == "kanban")? true: false;
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
		if (isset($refs[1]) && !empty($refs[1]) && $refs[1] != 'kanban'){
			$_GET['issue'] = $refs[1];
		}

		if (!empty($refs[0])){
			$_GET['kanban'] = ($refs[1] == "kanban")? true: false;
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
	$str = preg_replace('/((?<!\|)(http|ftp|https)):\/\/([\w\-_]+(?:(?:\.[\w\-_]+)+))([\w\-\.,@?^=%&amp;:\/~\+#]*[\w\-\@?^=%&amp;\/~\+#])?/s',
                            '<a href="$2://$3$4" target=_blank rel="nofollow" class="autolink">$2://$3$4</a>',$str); // Introduced in JILS-25


	// This one's specifically used by my commit notifications
	$str = preg_replace('/(\[View Commit\|)(.*?)(\])/s','<a href="$2" target=\_blank>View Commit</a>',$str);
	$str = preg_replace('/(\[View Changes\|)(.*?)(\])/s','<a href="$2" target=\_blank>View Changes</a>',$str);

        $str = preg_replace('/(\{quote\})(.*?)(\{quote\})/s','<blockquote>$2</blockquote>',$str);
        $str = preg_replace('/(\{noformat\})(.*?)(\{noformat\})/s','<pre>$2</pre>',$str);
        $str = preg_replace('/((?<!\\\)\*)([^\s].*?[^\s])((?<!\\\)\*)/s','<b>$2</b>',$str); // Bolds
        $str = preg_replace('/((^|\s)(?<!\\\)_)([^\s].*?[^\s])((?<!\\\)_(\b))/s','<i> $3 </i>',$str); // Italics - currently causes more trouble than I can be arsed with

	// Replace escaped characters
        $str = preg_replace('/((?<!\\\)\\\_)/s','_',$str);
	$str = preg_replace('/((?<!\\\)\\\\\\*)/s','*',$str);


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
	
	/* Disabled in JILS-20
	if ($pkey){
		$str = preg_replace("/($pkey\-)([0-9]*)/","<a href='".qs2sef("issue=$2&proj=$pkey")."'>$pkey-$2</a>",$str);
			
	}*/

	// See JILS-20
	$projects = buildProjectFilter(false, true);
	$projects[] = $pkey;
	

	$str = preg_replace_callback("/((".implode("|",$projects).")\-)([0-9]*)/",'linkIssueKey',$str);

	// See JILS-27
	$str = preg_replace_callback("/(([A-Z0-9._%-\+]+)@([A-Z0-9_%-]+)\.([A-Z\.]{2,20}))/i",'obscureEmail',$str);


	// User mentions (see JILS-4) e.g. [~ben]
	$str = preg_replace_callback("/(\[~)(.*?)(\])/s","embedUserLink",$str);



	return $str;
}


/** See JILS-4 - Specifically designed for user mentions within comments
*
*/
function embedUserLink($match){
	return translateUser($match[2]);
}


/** See JILS-27
*
*/
function obscureEmail($match){

	global $conf;
	$parts = explode('@',$match[0]);


	switch (strtolower($conf->emailObfs)){

		case 'part':
			$str = "{$parts[0]}@&lt;Domain Hidden&gt;";
			break;

		case 'full':
			$str = "&lt;Email Hidden&gt;";
			break;

		case 'none':
			$str = $match[0];
			break;

		default:
			$u = '';
			for ($i = 0; $i < strlen($parts[0]); $i++){
				$u .= '&#' . ord($parts[0][$i]) . ';';
			}

			$d = '';
			for ($i = 0; $i < strlen($parts[1]); $i++){
				$d .= '&#' . ord($parts[1][$i]) . ';';
			}


			$str = "<script type='text/javascript'>\n".
				"document.write('$u');\n" .
				"document.write('@');\n\n" .
				"document.write('$d');" .
				"</script>";
	}

	return $str;
}



/** See JILS-20
*
*/
function linkIssueKey($m){
  $m[1] = rtrim($m[1],"-");
  return "<a href='".qs2sef("issue={$m[3]}&proj={$m[2]}")."'>{$m[2]}-{$m[3]}</a>";
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

  $to_check = $_SERVER['REMOTE_ADDR'];

  // Introduced for JILS-37
  if (count($conf->AuthorisedProxies) > 0 && in_array($to_check,$conf->AuthorisedProxies) && isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
	  // Connection came from an authorised proxy. Use X-Forwarded-For
	  $entries = explode(",",$_SERVER['HTTP_X_FORWARDED_FOR']);
	  $to_check = $entries[0];
	  unset($entries);
  }


  // Limit IP to Projects
  if (array_key_exists("a".$to_check,$conf->IPProjectRestrictions)){
      $_SERVER['HTTP_X_PROJECT_LIMIT'] = $conf->IPProjectRestrictions["a".$to_check];
  }elseif(!isset($_SERVER['HTTP_X_PROJECT_LIMIT']) || empty($_SERVER['HTTP_X_PROJECT_LIMIT'])){
      $db = new BTDB;
      $sql = 'SELECT pkey FROM project';
      $db->setQuery($sql);
      $projects = $db->loadResults();

      $keys = array();

      foreach ($projects as $pr){
		$keys[] = $pr->pkey;
      }
      $_SERVER['HTTP_X_PROJECT_LIMIT'] = implode(",",$keys);
  }


  // Set the email Obfuscation level if there is a configured level specific to that client
  if (array_key_exists("a".$to_check,$conf->IPemailObfs)){
      $conf->emailObfs = $conf->IPemailObfs["a".$to_check];
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

	// See JILS-17 - Add semantic markup
	//$user_string = $user->first_name . " " . $user->last_name;

	$str = "<div style='display: inline' itemscope itemtype='http://schema.org/Person'>";

	if ($user){
		$str .= "<span itemprop='givenName'>{$user->first_name}</span> <span itemprop='familyName'>{$user->last_name}</span>";
	}else{
		$str .= 'Unassigned';
	}
		

	$str .= "</div>";

	return $str;


    }else{
	return "<div style='display: inline' itemscope itemtype='http://schema.org/Person'><span itemprop='alternateName'>$username</span></div>";
    }

}


/** Seems a bit overblown having a seperate function for something so simple, but it's likely going to do a lot more in future!
*
*/
function expandUserRecord($user){

      $str = "<div style='display: inline' itemscope itemtype='http://schema.org/Person'>";

      if (isset($user['URL']) && !empty($user['URL'])){
	    $str .= "<a class='extauthorLink' title='View Profile (external site)' href='{$user['URL']}' itemprop='alternateName' target=_blank>{$user['DisplayName']}</a>".
		    "<meta itemprop='url' content='{$user['URL']}' />".
		    "<meta itemprop='name' content='{$user['DisplayName']}' />";
      }else{
	    $str .= "<span itemprop='alternateName'>{$user['DisplayName']}</span>";
      }

      $str .= "</div>";
      return $str;
}



/** Check the script status - fairly simplistic check for now
*
* See JILS-24
*/
function check_status(){
      global $conf;

      if ($conf->maintenance){
	echo "IN MAINTENANCE";
	return;
      }

      $db = new BTDB;
      $db->setQuery('SELECT COUNT(*) AS issuecount from jiraissue');
      $res = $db->loadResult();

      if (!$res){
	  echo "QUERY FAILED";
      }else{
	  echo "QUERY SUCCESS";
      }

}


function getOriginalKey($key,$db){

	$key = $db->stringEscape($key);
	$db->setQuery("SELECT ORIGINALKEY from project where pkey='".$key."'");
	$p = $db->loadResult();
	return $p->ORIGINALKEY;

}


/** Create a HTML table based on Timespent and Estimate
*
*/
function createTimeBar($timespent,$remaining=0,$originalestimate=0,$showtime=true){

    // We need to work out which is bigger, so that we can scale correctly
    $reference = ($timespent > $originalestimate)? $timespent : $originalestimate;

    // Check we've actually got something to output
    if ($reference == 0){
	return '';
    }
    $display = ($showtime)? 'inline-block' : 'none';

    $timespent_perc = round(($timespent / $reference) * 100);
    $estimate_perc = round(($originalestimate / $reference) * 100);

    $est_null = 100 - $estimate_perc;
    $ts_null = 100 - $timespent_perc;

    $est_ndisp = ($est_null == 0)? 'none' : 'table-cell';
    $ts_ndisp = ($ts_null == 0)? 'none' : 'table-cell';

    $est_disp = ($est_null == 100)? 'none' : 'table-cell';
    $ts_disp = ($ts_null == 100)? 'none' : 'table-cell';

    $est_txt = ($originalestimate == 0)? 'Not Provided' : ($originalestimate / 60) . " minutes";
    $ts_txt = ($timespent / 60) . " minutes";

    // Generate the bar for original estimate
    $htmlstr = "<span class='timegraphlbl' style='display: $display'>Estimated:</span>" .
    "<table class='timegraph' title='Estimated: $est_txt'>".
    "<tr class='estimate'>".
    "<td class='logged' style='display: $est_disp; width: $estimate_perc%;'>&nbsp;</td>".
    "<td class='notlogged' style='display: $est_ndisp; width: $est_null%;'>&nbsp;</td>".
    "</tr>".
    "</table>".
    "<span class='timegraphannota' style='display: $display'>$est_txt</span>".
    "<div class='clr'></div>";
    
    if ($showtime){
	// Generate the Bar for estimated remaining time
	$remaining_perc = round(($remaining / $reference) * 100);
	$rem_null = 100 - $remaining_perc;
	$rem_ndisp = ($rem_null == 0)? 'none' : 'table-cell';
	$rem_disp = ($rem_null == 100)? 'none' : 'table-cell';

	$tr_txt = ($remaining == 0)? '0' : ($remaining / 60);

	$htmlstr .= "<span class='timegraphlbl' style='display: $display'>Remaining:</span>" .
	"<table class='timegraph' title='Remaining: $tr_txt minutes'>".
	"<tr class='remaining'>".
	"<td class='notlogged' style='display: $rem_ndisp; width: $rem_null%;'>&nbsp;</td>".
	"<td class='logged' style='display: $rem_disp; width: $rem_perc%;'>&nbsp;</td>".
	"</tr>".
	"</table>".
	"<span class='timegraphannota' style='display: $display'>$tr_txt minutes</span>".
	"<div class='clr'></div>";
    }

    // Generate the bar for time logged
    $htmlstr .= "<span class='timegraphlbl' style='display: $display'>Logged:</span>" .
    "<table class='timegraph' title='Logged: $ts_txt'>".
    "<tr class='recorded'>".
    "<td class='logged' style='display: $ts_disp; width: $timespent_perc%;'>&nbsp;</td>".
    "<td class='notlogged' style='display: $ts_ndisp; width: $ts_null%;'>&nbsp;</td>".
    "</tr>".
    "</table>".
    "<span class='timegraphannota' style='display: $display'>$ts_txt</span>".
    "<div class='clr'></div>";


    return $htmlstr;
	
}


/*** Check whether the request is conditional, if it is, then evaluate the headers and behave accordingly
*
* Introduced in JILS-41
*
* @arg mtime - Last-Modified for the copy in the database
* @arg etag  - Generated Etag for the copy in the database
*
* @return mixed
*/
function evaluateConditionalRequest($mtime,$etag){

  // Only honour conditionals for HEAD/GET
  if ((stripos($_SERVER['REQUEST_METHOD'], 'HEAD') === FALSE) && stripos($_SERVER['REQUEST_METHOD'], 'GET') === FALSE) {
	  return false;
  }


  if (isset($_SERVER['HTTP_IF_NONE_MATCH'])){
	  // There may be several etags
	  $etag_candidates=explode(",",$_SERVER['HTTP_IF_NONE_MATCH']);
	  foreach ($etag_candidates as $cand){
	      // Remove any whitespace from the header
	      $cand=trim($cand);
	      if ("$cand" == "$etag"){
		      returnNotModified($etag);
	      }
	  }

  }elseif(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])){
	  // Convert to epoch
	  $lastmod = strtotime($mtime);

	  // Validate the date is a HTTP date
	  if (validateDate($_SERVER['HTTP_IF_MODIFIED_SINCE'], 'D, d M Y H:i:s T')){
		$candtime = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);

		if ( $lastmod <= $candtime && $lastmod != 0 ){
			  returnNotModified(false,$mtime);
		}
	  }

  }

  return true;
}

/** Set the status code to 304. Also resend Etag and Last-mod as Apache strips them out when we change the status
*
* Introduced in JILS-41
*
*/
function returnNotModified($etag,$lastmod=false){
    header("HTTP/1.1 304 Not Modified",true,304);

    // Only include an etag if it revalidated (otherwise Apache will strip the Last-Mod
    if ($etag){
	header("ETag: $etag");
    }


    if ($lastmod){
	header("Last-Modified: $lastmod");
    }
    exit();
}


// Shamelessly nabbed from http://php.net/manual/en/function.checkdate.php/#113205
function validateDate($date, $format = 'Y-m-d H:i:s')
{

    // Ensure the date is in GMT (RFC2616)
    $date=rtrim($date);
    $tz=substr($date,-3,3);

    if ($tz != "GMT"){
	  return false;
    }

    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

