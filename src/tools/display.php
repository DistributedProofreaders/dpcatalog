<?php
$relPath="../includes/";
require_once($relPath.'common.inc');

// ==========================================================================
// Retrieve and sanitize URL parameters
// $id is the OLS id number of the title we're interested in
// $page in URL parameters specifies what page image we want to display.
// $jumpto replaces $page if we're jumping to a specific image
// It can be missing if the user is navigating via prev/next first/last
// buttons, but replaces $page if present, indicating use of the jumpto form.
//
// $page and $jumpto (if used) are validated once the script has the array
// of image file names.

$id     = validate_projectID( 'id', $_GET['id'], FALSE);
$page   = array_key_exists('page', $_GET) ? $_GET['page'] : null;
$jumpto = array_key_exists('jumpto', $_GET) ? $_GET['jumpto'] : null;

if ($jumpto != '')
{
    $page=$jumpto;
}

// Connect to database
    $db_Connection = new dbConnect()
        or die(_("Failed to connect to database."));

$qid = mysqli_real_escape_string($db_Connection->connection,$id);

// If the project is blacklisted, pretend this doesn't exist
if(is_project_blacklisted($id))
    $qid = 'BLACKLIST';

$sql = "SELECT
        author_name, title, page_image_location
        FROM catalog
        WHERE id = '$qid'";
$result = mysqli_query($db_Connection->connection,$sql);

if ( mysqli_num_rows($result) != 1)
{
    // If we fail to find the requested id in the catalog, we DO want to generate
    // a user-friendly error page using the template.

    // ==========================================================================
    // Set up page template

    $page = new HtmlTemplate();
    $page->IdentifyTemplate ($templatePath."PageTemplate.inc");

    // ==========================================================================
    // Fill in page template

    $page->SetParameter ("PageTitle",       $site_name . " - " . _("Display Images"));
    $page->SetParameter ("PageHeader",      _("Display Images"));
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
    exit;
}
else
{
    $finfo  = mysqli_fetch_array($result,MYSQLI_ASSOC);
    $author = html_safe($finfo['author_name']);
    $title  = html_safe($finfo['title']);
    $page_image_location = $finfo['page_image_location'];
}

// TODO: Fix hardcodes
$bookdir = "http://www.pgdp.org".$page_image_location."".$id."/".$archive_pages_subdir;

// Pull in the list of image files from the projects web_ready directory.
// This is inefficient, but since we do not store a page list, cannot rely
// on the presence or content of a DP-generated concatenated text file,
// and cannot make assumptions about what page-naming convention was used
// in the project, we simply pull a new list from the filesystem at page load.
//

// Note that $imagedir is expected to be a filesystem location
// TODO: Fix slashmess
$imagedir = $archive_paths_prefix."/".$page_image_location."".$id."/".$archive_pages_subdir;
$image_array = scanDirectoryImages($imagedir);
$numpages = count($image_array);

// VALIDATION:
// If page is not specified in the URL,
// OR is not in the array of page image filenames retrieved by the code,
// set it to the first page image.
$page_key = array_search($page,$image_array);
if ($page == '' || !array_search($page,$image_array))
{
    $page_key = 0;
    $page = $image_array[$page_key];
}

// Set up prev/next, first/last keys
$first_key = 0;
$last_key  = count($image_array)-1; // Offset necessary due to 0/1 indexing

$prev_key  = $page_key-1;
if ( $prev_key < $first_key )
{
    $prev_key = $first_key;
}

$next_key  = $page_key+1;
if ( $next_key > $last_key )
{
    $next_key = $last_key;
}

// And their values
$prevpage  = $image_array[$prev_key];
$nextpage  = $image_array[$next_key];
$firstpage = $image_array[$first_key];
$lastpage  = $image_array[$last_key];

// Generate Image Navigation Controls
// Form select/option controls

$html = '';
$page_list  = '';
$page_list .= '<form method="get" action="display.php" id="jumpbox">'."<div>\n";
$page_list .= '<input type="hidden" name="id" value="'.$id.'" />'."\n";
$page_list .= '<select id="pagelist" name="jumpto">'."\n";


foreach ( $image_array as $imagefile)
{
    $selected_option = '';
    if ($imagefile == $page)
    {
        $selected_option = ' selected="selected"';
    }
    $page_list .= '<option label="'.$imagefile.'" value="'.$imagefile.'"'.$selected_option.'>'.$imagefile.'</option>'."\n";
}
$page_list .= '</select><input type="submit" value="Jump" size="3" />';
$page_list .= '</div></form>';

// Buttons

$nav_controls = '';

$nav_controls .= "<table id='pagenav'>\n";
$nav_controls .= "<tr><td class='nav_large'>$page</td>"; // Iffy

if ($prev_key >= 0)
{
    $nav_controls .= "
    <td><a href = \"display.php?page=$firstpage&amp;id=$id\">
    <img src = \"../graphics/fastback_arrow.gif\" alt = \"start\" /></a></td>

    <td><a href = \"display.php?page=$prevpage&amp;id=$id\">
    <img src = \"../graphics/back_arrow.gif\" alt = \"back\" /></a></td>";
}
else
{
    $nav_controls .= "<td></td><td></td>";
}

if ($next_key <= $numpages)
{
    $nav_controls .= "
    <td><a href = \"display.php?page=$nextpage&amp;id=$id\">
    <img src = \"../graphics/forward_arrow.gif\" alt = \"next\" /></a></td>

    <td><a href = \"display.php?page=$lastpage&amp;id=$id\">
    <img src = \"../graphics/fastforward_arrow.gif\" alt= \"end\" /></a></td>
    ";
}
else
{
    $nav_controls .= "<td></td><td></td>
    ";
}

$nav_controls .= "<td class='nav_large'>$page_list</td>";
$nav_controls .= "</tr></table>\n\n";

$imagefile = $page;

$image_block = '';
$image_block .= "<img class='pageimage' src='$bookdir/$imagefile' width='50%' alt='".$imagefile."' />\n";

$html .= $nav_controls;
$html .= $image_block;

// ==========================================================================
// Set up page template

$page = new HtmlTemplate();
$page->IdentifyTemplate ($templatePath."PageTemplate.inc");

// ==========================================================================
// Fill in page template

$page->SetParameter ("PageTitle",       $site_name . ": " . $title . " by " . $author);
$page->SetParameter ("PageHeader",      "\"$title\" by $author");
$page->SetParameter ("SiteBase",        $site_base);
$page->SetParameter ("ParentSiteBase",  $parent_url);
$page->SetParameter ("ParentSiteName",  $parent_name);
$page->SetParameter ("ProjectStats",    ProjectStats());
$page->SetParameter ("BookStats",       BookStats());
$page->SetParameter ("SearchBlock",     show_search(FALSE) );

$extras = array( array("text" => _("Bibliographic Record"), "url" => "biblio.php?id=$id", "active" => ""));

$page->SetParameter ("NavigationBlock", show_navbar('', TRUE, TRUE, $extras ) );
$page->SetParameter ("PageBody",        $html );
$page->SetParameter ("PageSubContent",  $site_disclaimer);
$page->SetParameter ("BuildTime",       script_end_time($starttime));

$page->CreatePage();

// ==========================================================================

/*
* Recursively search through directory for images and create an array of the
* filenames.
*
* @param  array  $exts      (what file extensions are being searched for)
* @param  string $directory (the filesystem path we are searching in)
* @return array
*/

function scanDirectoryImages($directory, array $exts = array('jpg', 'png'))
{
    $files_array = array();
    if ( substr($directory, -1) == '/')
    {
        $directory = substr($directory, 0, -1);
    }

    if ( is_readable($directory)
         && (file_exists($directory) || is_dir($directory)) )
    {
        $directoryList = opendir($directory);
        while ($file = readdir($directoryList))
        {
            if ($file != '.' && $file != '..')
            {
                $path = $directory . '/' . $file;
                if (is_readable($path))
                {
                    if (is_dir($path))
                    {
                        return scanDirectoryImages($path, $exts);
                    }
                    $t0  = explode('/', $path);
                    $t00 = explode('.', end( $t0 ));
                    $t1 = end( $t00 );
                    if ( is_file($path)
                         && in_array( $t1, $exts) )
                    {
                        $files_array[] = end($t0);
                    }
                }
            }
        }
        closedir($directoryList);
        natsort($files_array);
    }
    // Rekey the array from zero for clarity and ease of navigation
    return array_values(array_unique($files_array));
}

// vim: sw=4 ts=4 expandtab
