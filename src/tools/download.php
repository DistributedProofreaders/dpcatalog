<?php
$relPath="../includes/";
require_once($relPath.'common.inc');

// On the KISS principle, we only offer one download option:
// - All image files associated with a project (pages and illustrations).

// ==========================================================================
// Retrieve and sanitize URL parameters
// $id is the OLS id number of the title we're interested in

$id = validate_projectID( 'id', $_GET['id'], FALSE);

// If we don't have a validly formatted id, the validation above will error it
// out messily. But later on we want to error out cleanly if there's an issue.

// Get the project directory

$db_Connection = new dbConnect()
    or die(_("Failed to connect to database."));

$qid = mysqli_real_escape_string($db_Connection->connection,$id);

// If the project is blacklisted, pretend this doesn't exist
if(is_project_blacklisted($id))
    $qid = 'BLACKLIST';

$sql = "SELECT
        page_image_location
        FROM catalog
        WHERE id = '$qid'
        LIMIT 1";
$result = mysqli_query($db_Connection->connection,$sql);
$count = mysqli_num_rows($result);

if ( $count == 0)
{
    // If we fail to find the requested id in the catalog, we DO want to generate
    // a user-friendly error page using the template.

    // ==========================================================================
    // Set up page template

    $page = new HtmlTemplate();
    $page->IdentifyTemplate ($templatePath."PageTemplate.inc");

    // ==========================================================================
    // Fill in page template

    $page->SetParameter ("PageTitle",       $site_name . " - " . _("Downloads"));
    $page->SetParameter ("PageHeader",      _("Downloads"));
    $page->SetParameter ("SearchBlock",     show_search(TRUE) );
    $page->SetParameter ("NavigationBlock", show_navbar('', TRUE, TRUE) );
    $page->SetParameter ("PageBody",        "<p>"._("The requested catalog record does not exist.")."</p>");
    $page->SetParameter ("PageSubContent",  $site_disclaimer);
    $page->SetParameter ("ProjectStats",    ProjectStats());
    $page->SetParameter ("BookStats",       BookStats());
    $page->SetParameter ("SiteBase",        $site_base);
    $page->SetParameter ("ParentSiteBase",  $parent_url);
    $page->SetParameter ("ParentSiteName",  $parent_name);
    $page->SetParameter ("BuildTime",       script_end_time($starttime));

    $page->CreatePage();
}
else
{
    $finfo               = mysqli_fetch_array($result,MYSQLI_ASSOC);
    $page_image_location = $finfo['page_image_location'];

    // Suppress display -- we're streaming back a zipfile, and we want
    // to control the http headers

    // Construct the path to the directory containing
    // the archive item and chdir() into it.
    // Note that the path constructions used here intentionally
    // preserve the directory structure in the zip files hierarchy.

    $parent_dir = "$archive_paths_prefix/$page_image_location/";
    chdir($parent_dir);

    // Get list of image files in the archive directory itself.
    $nonpage_image_names = glob("$id/*.{png,jpg}", GLOB_BRACE);

    // Get list of image files in the subdirectory.
    $page_image_names = glob("$id/$archive_pages_subdir/*.{png,jpg}", GLOB_BRACE);

    // Create a temporary file to fill with a list of the
    // files to include in the zip file we will send.
    $files_list = tempnam( sys_get_temp_dir(), 'ALL');

    // Add the files in the parent to the list
    $files = implode(chr(10),$nonpage_image_names);
    file_put_contents( $files_list, $files );

    // Add the files in the web_ready subdir to the list
    $files = implode(chr(10),$page_image_names);
    file_put_contents( $files_list, $files, FILE_APPEND );

    // Create the zip on-the-fly and stream it back to the browser
    $zipfile = $id . "_allimages.zip";
    header('Content-type: application/zip');
    header('Content-Disposition: attachment; filename="'.$zipfile.'"');
    header('Content-Transfer-Encoding: binary');
    header("Cache-Control: no-cache, must-revalidate");
    header("Expires: Sat, 30 Sep 2000 23:59:59 GMT");
    // TODO: Needs to be cross-platform - this is UNIX-specific.
    passthru("cat $files_list |zip -@ -");

    // Remove our temporary file
    unlink($files_list);
}

// vim: sw=4 ts=4 expandtab
