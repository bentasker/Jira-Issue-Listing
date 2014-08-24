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


$conf->db = 'JiraDB';
$conf->host = 'localhost';
$conf->user = 'jiradbuser';
$conf->password = '';
$conf->dbprefix = '';

$conf->scriptname='index.php';
$conf->jiralocation = 'http://jira.example.com';
$conf->jirahome = '/var/atlassian/application-data/jira/'; // If you're not going to be using the archival script/attachment functionality, you can ignore this


// Authorisation params - Any request with the wrong UA, or not originating from an authorised IP will be redirected to JIRA
$conf->SphiderUA = array('Sphider','Jira-Project-Archive'); // Include any user-agents that are allowed to view these pages
$conf->SphiderIP = array('192.168.1.65/32','192.168.1.96'); // You can use CIDR or specify individual IPs


$conf->debug = false; // Enabling this will prevent redirection to JIRA
