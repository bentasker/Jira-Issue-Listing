<?php
/** JIRA Issue List Script - Configuration
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

date_default_timezone_set('Europe/London');
$conf->db = 'JiraDB';
$conf->host = 'localhost';
$conf->user = 'jiradbuser';
$conf->password = '';
$conf->dbprefix = '';

$conf->scriptname='index.php';
$conf->jiralocation = 'http://jira.example.com';
$conf->jirahome = '/var/atlassian/application-data/jira/'; // If you're not going to be using the archival script/attachment functionality, you can ignore this

$conf->usernames = 'name'; // Set this to username to leave as usernames
$conf->customUsernames = false; // Set this to true if you want to honour anything set in authors.php


/* Email obfuscation style - 

none, part, bot or full. 

Part will completely strip the domain name from the address, Full will fully remove the email address, and bot will simply obscure the address for bots (requires javascript), None will leave the address alone

See also IPemailObfs below
*/
$conf->emailObfs = 'bot'; 

// Works in a similar manner to the config value above, but allows the default to be overridden on a per-IP basis. As with IPProjectRestrictions, the IP address should be prefixed with a lowercase a
// example array('a1.1.1.1'=>'Full')
$conf->IPemailObfs = array();

// Authorisation params - Any request with the wrong UA, or not originating from an authorised IP will be redirected to JIRA
$conf->SphiderUA = array('Sphider','Jira-Project-Archive'); // Include any user-agents that are allowed to view these pages
$conf->SphiderIP = array('192.168.1.65/32','192.168.1.96'); // You can use CIDR or specify individual IPs

// IP's listed here are known authorised proxies. If the connection originates from here we'll trust X-Forwarded-For
// introduced in JILS-37
$conf->AuthorisedProxies = array();

// Limit requests for a given IP to a specific set of project keys, prefix the IP with a lowercase a
$conf->IPProjectRestrictions = array(); // Example: array('a192.168.1.2'=> 'FOO,BAR,CHAR');

// Set this to be a URL (relative or otherwise) if you want to include your own custom CSS. Otherwise, leave as false
$conf->cssURL = false;

$conf->debug = false; // Enabling this will prevent redirection to JIRA
$conf->maintenance = false; // Enabling this will force the status check to fail
