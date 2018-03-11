<?php
$relPath="../../../includes/";
include_once($relPath.'common.inc');

// NOTES:
// IF YOU ARE SETTING UP A NEW CATALOG/SITE, YOU DO NOT NEED THIS SCRIPT.
//
// IT IS INCLUDED IN THE REPOSITORY FOR HISTORICAL PURPOSES ONLY, AND MAY
// CONTAIN SYNTAX ERRORS, OR CODE WHICH IS OBSOLETE OR DEPRECATED, DEPENDING
// ON YOUR LOCAL ENVIRONMENT.
// ** USE AT YOUR OWN RISK.

// This script needs only be run once to update an existing catalog database
// which needs to have the DP-specific curly-brace enclosed {tags} removed
// from the book titles in the catalog records.

// This script assumes:
// - That you are updating an existing local catalog database which has
//   curly-brace enclosed 'tags' in the book titles.

// If you need to execute this script, comment out the following line,
// make any necessary php syntax changes necessary for your specific
// environment, and run this script on your site from a browser.

die(_("Disabled. Please see the source code for documentation."));

// ====================================================================
// MAIN SCRIPT

echo "<html><table border = \"1\">";
echo "<tr><th>Row</th><td>Project</td><td>Old Title</td><td>New Title</td></tr>";

// Connect to database
$db_Connection=new dbConnect();

// Grab the entire set of id, title from catalog in ols db in which title has a curly-braced tag.

$sql = ("SELECT id, title from catalog where title like '%{%'");
$result = mysql_query($sql);

$num_rows = mysql_num_rows($result);

for ( $rownum=0; $rownum < $num_rows; )
{
    $id    = mysql_result($result, $rownum, "id");
    $title = mysql_result($result, $rownum, "title");

    $newtitle = addslashes(clean_title($title));

    echo "<tr><th>$rownum</th><td>$id</td><td>$title</td><td>$newtitle</td></tr>\n";

    // Make updates to the OLS database
    $sql2 = "UPDATE catalog set title='$newtitle' where id='$id'";
    $nresult = mysql_query($sql2);

    $rownum += 1;
}

echo "</table></html>";

// ==========================================================================

/*
* This function removes text within curly braces from the project titles and
* cleans up whitespace in the resulting string.
* The intended use is to remove the useless (for cataloging) DP-specific tags
* which are artifacts from the ebook production process at DP.
* It may have unintended results if titles of works in your catalog contain
* legitimate strings enclosed in curly braces.
*
* @param  string $title (the title of the work)
* @return string        (title with the tags removed, and rtrim'med)
*/

function clean_title( $title )
{
    // Remove DP-specific {tags} from the titles
    $newtitle = preg_replace('/\{.+\}/', '', $title);
    // Collapse multiple whitespaces
    $newtitle = preg_replace( '/\s+/', ' ', $newtitle );
    // Remove trailing whitespace
    $newtitle = rtrim($newtitle);
    return $newtitle;
}

?>

