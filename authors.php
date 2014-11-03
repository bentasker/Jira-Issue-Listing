<?php
/** JIRA Issue List Script - Configuration
*
* Simple script to generate an simple HTML listing of JIRA Issues from a Private JIRA Instance
* Intended use is to allow indexing of JIRA issues by internal search engines such as Sphider (http://www.sphider.eu/)
*
* Documentation: http://www.bentasker.co.uk/documentation/development-programming/273-allowing-your-internal-search-engine-to-index-jira-issues
*
* Author information override file (implemented in JILS-14)
*
* @copyright (C) 2014 B Tasker (http://www.bentasker.co.uk). All rights reserved
* @license GNU GPL V2 - See LICENSE
*
* @version 1.2
*
*/



$authors = array();

/* Array key should be the letter 'a' followed by the JIRA username (case sensitive)

  - DisplayName - How should the usersname be displayed within issues?
  - URL - If not false, any instance of the users name will be a link to this URL
  - Avatar - Not currently supported, but may be used in future
  - CommentsHidden - Not currently supported. In future will be used to trigger a JS run to hide the user's comments by default
*/

$authors['auser'] = array(

    'DisplayName'=>"A User",
    'URL' => 'https://www.example.com',
    'Avatar' => '', //Not currently supported, but included for future use
    'CommentsHidden' => false //Not currently supported, but included for future use

);



