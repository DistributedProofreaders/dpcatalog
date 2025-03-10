<?php
$relPath="../includes/";
require_once($relPath.'common.inc');
require_once($relPath.'parse_pg_rdf.inc');

// ==========================================================================
// Retrieve and sanitize URL parameters
// $id is the OLS catalog identifier of the record we are interested in.
// It is the same as the DP projectID*.

$id = validate_projectID( 'id', $_GET['id'], FALSE);

// ==========================================================================
// Set up page template

$page = new HtmlTemplate();
$page->IdentifyTemplate ($templatePath."PageTemplate.inc");

// ==========================================================================
// Fill in page template

$page->SetParameter ("PageTitle",       $site_name . " - " . _("Bibliographic Records"));
$page->SetParameter ("PageHeader",      _("Bibliographic Records"));
$page->SetParameter ("SearchBlock",     show_search(TRUE) );
$page->SetParameter ("NavigationBlock", show_navbar('', TRUE, TRUE) );
$page->SetParameter ("PageBody",        displaybiblio($id, $pg_catalog_dir, $parent_url, $parent_name, $parent_abbr));
$page->SetParameter ("PageSubContent",  $site_disclaimer);
$page->SetParameter ("ProjectStats",    ProjectStats());
$page->SetParameter ("BookStats",       BookStats());
$page->SetParameter ("SiteBase",        $site_base);
$page->SetParameter ("ParentSiteBase",  $parent_url);
$page->SetParameter ("ParentSiteName",  $parent_name);
$page->SetParameter ("BuildTime",       script_end_time($starttime));

$page->CreatePage();

// ==========================================================================

/*
* Build the bibliographic record content for a given catalog id.
*
* @param  int    $id              (the catalog id of the desired title)
* @param  string $pg_catalog_dir  (filesystem path of the catalog files)
* @param  string $parent_url      (url of parent site, to create project page link)
* @return string                  (HTML)
*/

function displaybiblio ( $id, $pg_catalog_dir, $parent_url, $parent_name, $parent_abbr )
{
    // Connect to database
    $db_Connection = new dbConnect()
        or die(_("Failed to connect to database."));

    $qid = mysqli_real_escape_string($db_Connection->connection,$id);

    // Search for the id
    $sql = "SELECT
            id, pg_identifier, author_name, title, language,
            number_pages, image_source
            FROM catalog
            WHERE id = '$qid'";
    $result = mysqli_query($db_Connection->connection,$sql);

    $num_rows = mysqli_num_rows($result);
    if ( $num_rows != 1 )
    {
        // Invalid ID
        return "<p>"._("The requested catalog record does not exist.")."</p>";
    }

    $finfo         = mysqli_fetch_array($result,MYSQLI_ASSOC);
    $author        = html_safe($finfo['author_name']);
    $title         = html_safe($finfo['title']);
    $bookpages     = $finfo['number_pages'];
    $language      = html_safe($finfo['language']);
    $source_code   = html_safe($finfo['image_source']);
    $pg_identifier = html_safe($finfo['pg_identifier']);

    // If the image source is not internal, look up its display name and see
    // if we are allowed to redisplay their images.
    if ( $source_code == "_internal")
    {
        $display_name  = _("DP Internal");
        $ok_to_display = 1;
    }
    else
    {
        $qsource_code = mysqli_real_escape_string($db_Connection->connection,$source_code);

        $sql2 = "SELECT
                 display_name, ok_show_images
                 FROM image_sources
                 WHERE code_name = '$qsource_code'";
        $result2 = mysqli_query($db_Connection->connection,$sql2);

        $num_rows = mysqli_num_rows($result2);
        if ( $num_rows != 1 )
        {
            // Image source is missing?
            $display_name  = _("UNKNOWN");;
            $ok_to_display = 0;
        }
        else
        {
            $finfo         = mysqli_fetch_array($result2,MYSQLI_ASSOC);
            $display_name  = $finfo['display_name'];
            $ok_to_display = $finfo['ok_show_images'];
        }
    }

    // Build the link to the DP project page
    $dp_title_link = "$parent_url/c/project.php?id=$id&amp;detail_level=1";

    $table_part1 = "<table class='biblio'>\n
             <tr><td colspan='2'>"._("Local Record")."</td></tr>\n
             <tr><td class='right btitle' colspan='2'>$title</td></tr>\n
             <tr><td class='right' colspan='2'>$author</td></tr>\n
             <tr><td class='right' colspan='2'>$bookpages pp.</td></tr>\n";

    $table_part2 = "
             <tr><th>"._("Language")."</th><td>$language</td></tr>\n
             <tr><th>"._("Image Source")."</th><td>$display_name</td></tr>\n
             <tr><th>"._("Available Formats")."</th>\n";

    if ($ok_to_display==1)
    {
        $table_part3 = "<td><a href = \"{SiteBase}/tools/display.php?id=$id\">"._("Read by images")."</a></td>";
    } else {
        $table_part3 = "<td>"._("Display of images from this source is not permitted.")."</td>";
    }

    $table_part3 .= "</tr>\n";
    $table_part3 .= "<tr><th>"._("$parent_abbr Page")."</th><td><a href='$dp_title_link'>"._("View this project at $parent_name")."</a></td></tr>\n";

    // Could add a link to the project's dc.xml file, or parse it, but they
    // are not consistent. Older files are not the same format as current
    // ones, and the data quality is poor.

    $table_part4 = "</table>";

    // Add download link for images associated with the project.
    $table_part5 = "<p class='center highlight'>
                    <a href='{SiteBase}/tools/download.php?id=$id'>"._("Download project images")."</a></p>\n";

    $searchresults = $table_part1.$table_part2.$table_part3.$table_part4.$table_part5;

    // If the PG catalog directory isn't set, don't make an attempt
    if ( $pg_catalog_dir === NULL )
    {
        return $searchResults;
    }
    // Otherwise, attempt to pull the PG rdf info
    else
    {
        $pg_bibrec = display_pg_biblio($pg_identifier,$pg_catalog_dir);
        $searchresults .= $pg_bibrec;
    }

    return $searchresults;
}

/*
* Retrieve the filesystem path to a projects files from the catalog db.
*
* @param  int    $id (the catalog id of the desired title)
* @return string
*/

function get_project_dir( $id )
{
    // Connect to database
    $db_Connection = new dbConnect()
        or die(_("Failed to connect to database."));

    $qid = mysqli_real_escape_string($db_Connection->connection,$id);

    $sql = "SELECT
            page_image_location
            FROM catalog
            WHERE id = '$qid'";
    $result      = mysqli_query($db_Connection->connection,$sql);
    $finfo       = mysqli_fetch_array($result,MYSQLI_ASSOC);
    $project_dir = $finfo['page_image_location'];

    $db_Connection->close();
    return $project_dir;
}

/*
* Create an HTML table containing bibliographic information from the
* relevant rdf file in the PG catalog.
*
* @param  int    $id              (the PG number of the desired title)
* @param  string $pg_catalog_dir  (filesystem path of the catalog files)
* @return string                  (HTML)
*/

function display_pg_biblio( $pg_id, $pg_catalog_dir )
{
    $rdf_info = "\n\n<table class='biblio'>\n";

    // Try to get the PG information from the RDF catalog file
    $pg_array = parse_pg_catalog_rdf($pg_id, $pg_catalog_dir);

    if ( empty($pg_array) )
    {
        // We didn't find an RDF file for this title
        $rdf_info .= "<tr><td colspan='2'>"._("PG Catalog Record").": "._("Not Found")."</td></tr>\n";
    }
    else
    {
        $pg_title    = $pg_array['title']; // This is already HTML entitized
        $pg_name     = html_safe($pg_array['name']);
        $pg_subject  = html_safe($pg_array['subject']);
        $pg_language = html_safe($pg_array['language']);
        $pg_rights   = html_safe($pg_array['rights']);
        $pg_shelves  = html_safe($pg_array['shelves']);
        $pg_link     = $pg_array['pg_link'];

        $rdf_info .= "<tr><td colspan='2'>"._("PG Catalog Record")."</td></tr>\n";
        $rdf_info .= "<tr><td class='right btitle' colspan='2'>".$pg_title."</td></tr>\n";
        $rdf_info .= "<tr><td class='right' colspan='2'>".$pg_name."</td></tr>\n";
        // Note to translators: "DC" is "Dublin Core"
        $rdf_info .= "<tr><th>"._("DC Language")."</th><td>".$pg_language."</td></tr>\n";
        $rdf_info .= "<tr><th>"._("DC Rights")."</th><td>".$pg_rights."</td></tr>\n";
        $rdf_info .= "<tr><th>"._("DC Subject")."</th><td>".$pg_subject."</td></tr>\n";
        $rdf_info .= "<tr><th>"._("PG Bookshelves")."</th><td>".$pg_shelves."</td></tr>\n";
        $rdf_info .= "<tr><th>"._("PG Page")."</th>";
        $rdf_info .= "<td><a href='$pg_link'>"._('View this title at Project Gutenberg')."</a></td></tr>\n";
    }
    $rdf_info .= "</table>\n";

    return $rdf_info;
}

// TODO: Implement stub
// This function is intended to eventually display a cover image thumbnail
// (or perhaps a default graphic) on the biblographic page.
function find_cover_image( $id )
{
    $project_dir = get_project_dir($id);

    // Look for typical book cover image filenames in the project's archive directory
    // i.e., jpg or png named cover, fcover ...
    // A better approach would work use image metadata, when/if available.

    $cover_image = '';
    return $cover_image;
}

// vim: sw=4 ts=4 expandtab
