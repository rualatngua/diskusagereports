<?php

/* 
 * Copyright (c) 2011 André Mekkawi <diskusage@andremekkawi.com>
 * Version: $Source Version$
 * 
 * LICENSE
 * 
 * This source file is subject to the MIT license in the file LICENSE.txt.
 * The license is also available at http://diskusagereports.com/license.html
 */

if (!defined('E_DEPRECATED')) define('E_DEPRECATED', 8192);

// Disable warnings, notices and deprecation messages.
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);

require_once('inc/find.class.php');

$STDERR = fopen('php://stderr', 'w+');

if (php_sapi_name() != "cli") {
	fwrite($STDERR, "Must be run from the command line.\n"); exit(1);
}

if (!(function_exists("date_default_timezone_set") ? @(date_default_timezone_set('UTC')) : @(putenv("TZ=UTC")))) {
	echo fwrite($STDERR, "Timezone could not be set to UTC."); exit(1);
}

$find = new Find();
$directory = NULL;

$cliargs = array_slice($_SERVER['argv'], 1);
$syntax = "Syntax: php find.php [OPTIONS] <directory-to-scan>\nUse -h for full help or visit diskusagereports.com/docs.\n";

$syntax_long = <<<EOT
Syntax: php find.php [OPTIONS] <directory-to-scan>

<directory-to-scan>
The directory that the list of sub-directories and files will be created for.

The OPTIONS are:

      -d <delim>
      The field delimiter for each line in the output.
      The default is the NULL character.

      -ds <directoryseparator>
      The directory separator used between directory names.
      The default is the directory separator for the operating system.

See also: diskusagereports.com/docs


EOT;

while (!is_null($cliarg = array_shift($cliargs))) {
	$shifted = TRUE;
	
	switch ($cliarg) {
		case '/?':
		case '-?':
		case '-h':
		case '--help':
			fwrite($STDERR, $syntax_long);
			exit(1);
		/*case '-i':
			array_push($args['include'], $shifted = array_shift($cliargs));
			break;
		case '-e':
			array_push($args['exclude'], $shifted = array_shift($cliargs));
			break;*/
		case '-d':
			$find->setDelim($shifted = array_shift($cliargs));
			break;
		case '-ds':
			$find->setDS($shifted = array_shift($cliargs));
			break;
		
		default:
			$directory = $cliarg;
			//$cliargs = array();
	}
	
	if (is_null($shifted)) {
		fwrite($STDERR, "Missing value after argument $cliarg\n".$syntax);
		exit(1);
	}
}

// ==============================
// Validate and clean arguments
// ==============================

if (is_null($directory)) {
	fwrite($STDERR, "<directory-to-scan> argument is missing.\n".$syntax); exit(1);
}

switch($ret = $find->run($directory, NULL, $STDERR)) {
	case FIND_NOT_DIRECTORY:
		fwrite($STDERR, "The <directory-to-scan> does not exist or is not a directory.\n");
		break;
	case FIND_FAILED_RESOLVE:
		fwrite($STDERR, "Failed to resolve <directory-to-scan> to its full path. You may not have access (read and exec) to the directory or its parent directories.\n");
		break;
	case FIND_FAILED_STAT:
		fwrite($STDERR, "Failed to retrieve info (via stat) on <directory-to-scan>. You may not have access to the directory or its parent directories.\n");
		break;
}

fclose($STDERR);
exit($ret === TRUE ? 0 : 1);
?>
