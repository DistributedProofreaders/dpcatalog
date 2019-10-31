<?php
// This file sets up the vital infrastructure for the site, including:
// * including config.inc to set up global variables
// * setting up the page build time variables and end time function
// * defining the PageHtml class
// * pulling in the useful validation, etc. functions from misc.inc
// * pulling in the site stats functions
// * 
//
// This file should be included at the top of EVERY .php page. This
// is not a catch-all. The kitchen sink does not belong in here!
// Keeping this and its dependencies slim improves overall page
// performance.
//
// The order of the include_once()s in this file is important!

// TIMING: Set up variables to capture the script start time:
$mtime = explode(" ",microtime());
$starttime = $mtime[1] + $mtime[0];

// Override $relPath to be the absolute path to the directory that
// contains this file. This allows registered shutdown functions
// to know where to find files (working directory of the script
// can change inside the shutdown function based on PHP docs).
// This also helps things like APC which can bypass stat calls
// on includes that use absolute paths.
$relPath = dirname(__FILE__)."/";

// Set $templatePath to the location of the html templates
// Currently they are also located in the includes directory,
// but could be moved elsewhere by adapting this line.
$templatePath = $relPath;

// Pull in the various includes
require_once($relPath.'config.inc');        // Site variables

if(!headers_sent())
{
    header("Content-Type: text/html; charset=$charset");

    // Tell proxies to vary the caching based on the Accept-Language header
    // to ensure others behind the proxy don't inadvertantly get a language
    // they aren't expecting.
    header("Vary: Accept-Language");
}

require_once($relPath.'misc.inc');          // Useful generic functions
require_once($relPath.'connect.inc');       // db. Also pulls in udb_user.php
require_once($relPath.'PageHtml.class');    // Page templating functions
require_once($relPath.'PageControls.inc');  // Page navigation control code
require_once($relPath.'stats.inc');         // Site stats functions

// Undo magic quotes. This function enables the code to operate identically
// regardless of the setting of magic_quotes_gpc.
undo_all_magic_quotes();

// ==========================================================================

/*
* Returns the difference between a saved start time and the time the function
* is called as a formatted string. See TIMING section above.
*
* @param  string $starttime     (the starting time being measured against)
* @return string
*/
function script_end_time($starttime)
{
    $mtime     = explode(" ",microtime());
    $endtime   = $mtime[1] + $mtime[0];
    $totaltime = ($endtime - $starttime);
    $buildtime = " ("._("Page Build Time").": ".substr($totaltime, 0, 5).") ";
    return $buildtime;
}

/*
* Checks if $value contains a valid project ID, in either long or short form:
* projectID####, or #### respectively.
* Valid values are simply returned, otherwise dies with a warning.
*
* @param  string  $param_name (name of value being checked, used in error msg)
* @param  string  $value      (the value being validated)
* @param  boolean $allownull  (boolean)
* @return string  or NULL
*/
function validate_projectID($param_name, $value, $allownull = FALSE)
{
    if (!isset($value) && $allownull)
        return NULL;
    if (1 == preg_match('/^projectID[0-9a-f]{13}$/', $value))
        return $value;
    if (1 == preg_match('/^[0-9a-f]{13}$/', $value))
        return "projectID$value";
    die(sprintf(
        _("The value of parameter '%1\$s' ('%2\$s') is not a valid projectID."),
        html_safe($param_name),
        html_safe($value)
    ));
}

// vim: sw=4 ts=4 expandtab
