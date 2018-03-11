<?php
$relPath="../../includes/";
include_once($relPath.'common.inc');

// NOTES:
// IF YOU ARE SETTING UP A NEW CATALOG/SITE, YOU DO NOT NEED THIS SCRIPT.
//
// IT IS INCLUDED IN THE REPOSITORY FOR HISTORICAL PURPOSES ONLY, AND MAY
// CONTAIN SYNTAX ERRORS, OR CODE WHICH IS OBSOLETE OR DEPRECATED, DEPENDING
// ON YOUR LOCAL ENVIRONMENT.
// ** USE AT YOUR OWN RISK.

// This script only needs to be run if you are updating an existing
// catalog database which needs to have the image source values added
// to the catalog records for all of your projects.

// This script assumes:
// - That you are updating an existing local catalog database
//   from a remote source.
// - That your project archive files are under a single main directory.

$remote_server = "<<REMOTE_SERVER>>";
$remote_user   = "<<REMOTE_USER>>";
$remote_passwd = "<<REMOTE_PASSWORD>>";
$remote_dbname = "<<REMOTE_DATABASE>>";

// Local files location: e.g., "/path/to/project/directories/"
$local_files_directory = "<<LOCAL_FILES_DIR>>";

// If you need to execute this script, update the remote database
// and local directory variables above with the correct values for
// your installation, make any php syntax changes necessary for your
// specific environment, comment out the following line, and run this
// script on your site from a browser.

die(_("Disabled. Please see the source code for documentation."));

// ====================================================================
// MAIN SCRIPT

echo "<html><table border = \"1\">";
echo "<tr><td><b>Project</b></td><td>Image Source</td></tr>";

// Open the main archive directory and loop across subdirectory names
$handle = opendir($local_files_directory);

if ( !is_null($handle) ) {

    while ( ($file = readdir($handle)) !== FALSE ){

        if ($file != "." && $file != ".." && $file != ".listing") { 

            $ols_id = str_replace ("projectID", "", $file);
            $file = "projectID".$file;

            // Read image source values from remote database
            // NOTE: Still on mysql, not mysqli

            $db = mysql_connect($remote_server, $remote_user, $remote_password);
            mysql_select_db($remote_dbname,$db);

            $result = mysql_query("
                SELECT image_source
                FROM projects
                WHERE projectid = '$file'");

            $image_source = mysql_result($result, 0, "image_source") ."";
            echo "<td>$file</td><td>$image_source</td></tr>";

            // Make updates to catalog database
            // Connect to database
            $db_Connection = new dbConnect()
                or die(_("Failed to connect to database."));

            $sql = ("UPDATE catalog
                     set image_source = '$image_source'
                     WHERE id = '$ols_id'");
            $result = mysqli_query($db_Connection->connection,$sql);

            $index = 0;
        }
    }
   closedir($handle); 
}
else {
    echo _("Unable to open archives directory!\n");
}

echo "</table></html>";
?>
