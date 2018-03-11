<?php
$relPath="../includes/";
require_once($relPath.'common.inc');

// ==========================================================================
// Retrieve and sanitize URL parameters.
// searchtype is which field to search on.
// search is the term which is being searched for..

$search_types = array('author_name', 'title', 'pg_identifier', 'dpprojectid');
$searchtype = get_enumerated_param( $_GET, 'searchtype', 'title', $search_types );

// $searchterm is sanitized in the displaysearch() function call.
$searchterm = array_key_exists('search', $_GET) ? $_GET['search'] : null;

// ==========================================================================
// Set up page template

$page = new HtmlTemplate();
$page->IdentifyTemplate ($templatePath."PageTemplate.inc");

// ==========================================================================
// Fill in page template

$page->SetParameter ("PageTitle",       _("Open Library System - Search") );
$page->SetParameter ("PageHeader",      _("Search Results") );
$page->SetParameter ("NavigationBlock", show_navbar('', TRUE, FALSE) );
$page->SetParameter ("SearchBlock",     show_search(TRUE) );
$page->SetParameter ("PageBody",        displaysearch($searchtype, $searchterm) );
$page->SetParameter ("PageSubContent",  $site_disclaimer);
$page->SetParameter ("ProjectStats",    ProjectStats() );
$page->SetParameter ("BookStats",       BookStats() );
$page->SetParameter ("SiteBase",        $site_base);
$page->SetParameter ("ParentSiteBase",  $parent_url);
$page->SetParameter ("ParentSiteName",  $parent_name);
$page->SetParameter ("BuildTime",       script_end_time($starttime));

$page->CreatePage();

// ==========================================================================

/*
* Produces a list of catalog entries matching the given search criteria, as
* an HTML table.
*
* @param  string $searchtype  (which db field we are searching on)
* @param  string $searchterm  (the search term)
* @return string              (a valid block of HTML containing a table of
*                             results, or a paragraph element containing an
*                             error message.)
*/

function displaysearch($searchtype, $searchterm)
{
    if ($searchtype == '')
    {
        $searchresults = "<p class='highlight'>" . _("No search type was entered.") . "</p>";
        return $searchresults;
    }

    if ($searchterm == '')
    {
        $searchresults = "<p class='highlight'>" . _("No search term was entered.") . "</p>";
        return $searchresults;
    }

    // Avoid pointless searches (execution timeouts, large search result sets)
    if ( strlen($searchterm) < 3 )
    {
        $searchresults = "<p class='highlight'>" . _("Search term must be at least three characters long.") . "</p>";
        return $searchresults;
    }

    // Connect to database
    $db_Connection = new dbConnect()
        or die(_("Failed to connect to database."));

    // Sanitize the user-provided search term
    $sql_searchterm = "'%".mysqli_real_escape_string($db_Connection->connection,$searchterm)."%'";
    // Store a copy for later highlighting
    $search_string = $searchterm;

    if ($searchtype == 'dpprojectid')
    {
        $searchtype = 'id';
    }

    $order = $searchtype;
    if ( $searchtype == 'pg_identifier')
    {
        $order = "CAST(pg_identifier AS UNSIGNED)";
    }

    $sql = "SELECT
            id, author_name, title, pg_identifier
            FROM catalog
            WHERE $searchtype LIKE $sql_searchterm
            ORDER BY $order ASC";
    $result = mysqli_query($db_Connection->connection,$sql);
    $num_rows = mysqli_num_rows($result);

    $safeterm = html_safe($searchterm);
    if ($num_rows < 1)
    {
        $results_header = '';
        $searchresults =  "<p>"._("No results were found for: $safeterm.")."</p>";
    }
    else
    {
        $results_header = "<div id='pagination_header'>$num_rows results for: $safeterm.</div>";
        $table_part1 = "<table id='search_table'>\n";
        $table_part1 .= "<tr><th id='title'>"._("Title")."</th><th id='author'>"._("Author")."</th><th id='pg' class='right'>"._("PG No.")."</th></tr>";
        $table_part2 = '';

        for ( $rownum=0; $rownum < $num_rows; )
        {
            $finfo  = mysqli_fetch_array($result,MYSQLI_ASSOC);
            $id     = $finfo['id'];
            $author = html_safe($finfo['author_name']);
            $title  = html_safe($finfo['title']);
            $pg_id  = html_safe($finfo['pg_identifier']);

            // Highlight the search term.
            // Exclude DP id since it doesn't display and is used as a catalog identifier.
            switch ($searchtype)
            {
            case 'author_name':
                $author = highlight_search_term($author, $search_string);
                break;
            case 'title':
                $title = highlight_search_term($title, $search_string);
                break;
            case 'pg_identifier':
                $pg_id = highlight_search_term($pg_id, $search_string);
                break;
            default:
                break;
            }

            $table_part2 .= "<tr><td><a href =\"biblio.php?id=$id\">$title</a></td><td>$author</td><td class=\"right\">$pg_id</td></tr>\n";
            $rownum++;
        }

        $table_part3 = "</table>";
        $searchresults = $table_part1.$table_part2.$table_part3;
    }
    return $results_header.$searchresults;
}

/*
* Returns a string with classed span element surrounding the $searchterm.
*
* @param  string $string      (the string which is to have highlights added)
* @param  string $searchterm  (the search term to be highlighted)
* @return string              (a string with one or more embedded classed span elements)
*/

function highlight_search_term($string, $searchterm)
{
    // Escape any special characters
    $regexp = preg_quote($searchterm,"/");
    return preg_replace("/(".$regexp.")/i", '<span class=\'highlight\'>$1</span>', $string);
}

// vim: sw=4 ts=4 expandtab
?>

