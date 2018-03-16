<?php
$relPath="../includes/";
require_once($relPath.'common.inc');

// ==========================================================================
// Retrieve and sanitize URL parameters
// $browse determines which db field we are sorting by.
// $limit is the number of results to select and to display per page.
// $pn is the page number of the results page we're seeking

$browse_types = array( 'title', 'author_name', 'postednum');
$browse = get_enumerated_param( $_GET, 'browse', 'author_name', $browse_types, FALSE);
$limit  = get_integer_param( $_GET, 'limit', 25, 1, 25, FALSE);
$pn     = get_integer_param( $_GET, 'pn', 1, 1, NULL, FALSE);

// ==========================================================================
// Set up page template

$page = new HtmlTemplate();
$page->IdentifyTemplate ($templatePath."PageTemplate.inc");

$browseby = '';
switch ($browse)
{
    case "title":
        $page->SetParameter ("NavigationBlock", show_navbar('br_title', TRUE, FALSE) );
        $browseby = _("Title"); break;
    case "postednum":
        $page->SetParameter ("NavigationBlock", show_navbar('br_pgid', TRUE, FALSE) );
        $browseby = _("PG Number"); break;
    case "author_name":
    default:
        $page->SetParameter ("NavigationBlock", show_navbar('br_author', TRUE, FALSE) );
        $browseby = _("Author");
}

// ==========================================================================
// Fill in page template

$page->SetParameter ("PageTitle",      $site_name . " - " . _("Browse") );
$page->SetParameter ("PageHeader",     _("Browsing by")." $browseby");
$page->SetParameter ("SearchBlock",    show_search(TRUE) );
$page->SetParameter ("PageBody",       displaybrowse($browse, $limit, $pn));
$page->SetParameter ("PageSubContent", $site_disclaimer);
$page->SetParameter ("ProjectStats",   ProjectStats() );
$page->SetParameter ("BookStats",      BookStats() );
$page->SetParameter ("SiteBase",       $site_base);
$page->SetParameter ("ParentSiteBase", $parent_url);
$page->SetParameter ("ParentSiteName", $parent_name);
$page->SetParameter ("BuildTime",      script_end_time($starttime));

$page->CreatePage();


// ==========================================================================

/*
* Produces a subsetted list of catalog entries based on browse type and the
* desired page number of the results set, as an HTML table.
*
* @param  string $browse  (which db field we are sorting by)
* @param  int    $limit   (number of results to select/display per page)
* @param  int    $pagenum (page number of the results page we're seeking)
* @return string          (a valid block of HTML containing a table)
*/

function displaybrowse($browse, $limit, $pagenum)
{
    // Connect to database
    $db_Connection = new dbConnect()
        or die(_("Failed to connect to database."));

    // Which sort order and criteria do we want?
    //
    // The $where clause is normally empty.
    //
    // However, in the case of PG identifier, there may be projects which are
    // in the archive but were not posted to PG, i.e., a decision was made to
    // archive the project files to preserve the efforts of volunteers even
    // though it was unable to be completed.
    // Rather than suppress them in every browse option, we leave them visible
    // in author/title so a visitor can find them (if they know about them),
    // and only suppress their appearance in the PG listing where the lack of
    // an identifier is glaringly obvious.

    switch ($browse) {
        case "title":
            $where = "";
            $order = "title";
            break;
        case "author_name":
            $where = "";
            $order = "author_name";
            break;
        case "postednum":
            $where = "WHERE pg_identifier != ''";
            $order = "CAST(pg_identifier AS UNSIGNED INTEGER)";
            break;
        default:
            $where = "";
            $order = "author_name";
            break;
    }

    // Establish variables to hold various parts of the table display
    $table_part1 = "<table id='browse' width =\"100%\" >\n"; // Table Open
    $table_part2 = ""; // Table Header Row
    $table_part3 = ""; // Table Rows for Results
    $table_part4 = "</table>\n"; // Table Close

    // Get the total number of rows
    $sql = "SELECT COUNT(id) as numrecs FROM catalog $where";
    $result = mysqli_query($db_Connection->connection,$sql);
    $finfo  = mysqli_fetch_array($result);
    $num_rows = $finfo['numrecs'];

    // How many results we want per page
    $page_rows = $limit;

    // How many prev/next page links we want to display
    $next_pages = 4;

    // What is the last page
    $last_page = ceil($num_rows/$page_rows);
    // Ensure it's not < 1
    if ($last_page < 1 ) $last_page = 1;

    // pagenum is validated as $pn in the URL parameters and then passed
    // intothe function, so it should already meet these criteria.

    // Ensure pagenum isn't < 1 or > the last page
    if ($pagenum < 1) { $pagenum = 1; }
    else if ($pagenum > $last_page ) { $pagenum = $last_page; }

    // Set the row range we want for the specified page of results
    $limit = 'LIMIT ' .($pagenum - 1) * $page_rows .',' .$page_rows;

    // Build our query
    $sql = "SELECT
            id, author_name, title, pg_identifier
            FROM catalog
            $where
            ORDER BY $order ASC $limit";
    $result = mysqli_query($db_Connection->connection,$sql);
    $num_returned_rows = mysqli_num_rows($result);

    // Show total number of pages and current page number
    $header_1 = "<div>$num_rows Records: ";
    $header_2 = "Displaying page $pagenum (of $last_page)</div>\n";

    // Build the pagination control
    $paginationCtrls = '';

    // If there is more than 1 page worth of results
    if ($last_page != 1)
    {
        /* First we check if we are on page one. If we are then we don't need a link to 
           the previous page or the first page so we do nothing. If we aren't then we
           generate links to the first page, and to the previous page. */
        if ($pagenum > 1)
        {
            // Add a first page link
            $paginationCtrls .= '<a href="'.$_SERVER['PHP_SELF'].'?pn=1&amp;browse='.$browse.'">First</a> &nbsp; &nbsp; ';

            $previous = $pagenum - 1;
            $paginationCtrls .= '<a href="'.$_SERVER['PHP_SELF'].'?pn='.$previous.'&amp;browse='.$browse.'">Previous</a> &nbsp; &nbsp; ';


            // Render clickable number links that should appear on the left of the target page number
            for ($i = ($pagenum - $next_pages); $i < $pagenum; $i++)
            {
                if ($i > 0)
                {
                    $paginationCtrls .= '<a href="'.$_SERVER['PHP_SELF'].'?pn='.$i.'&amp;browse='.$browse.'">'.$i.'</a> &nbsp; ';
                }
            }
        }

        // Render the target page number, but without it being a link
        $paginationCtrls .= ''.$pagenum.' &nbsp; ';

        // Render clickable number links that should appear on the right of the target page number
        for ($i = $pagenum+1; $i <= $last_page; $i++)
        {
            $paginationCtrls .= '<a href="'.$_SERVER['PHP_SELF'].'?pn='.$i.'&amp;browse='.$browse.'">'.$i.'</a> &nbsp; ';
            if ($i >= ($pagenum + $next_pages) )
            {
                break;
            }
        }
        // This does the same as above, only checking if we are on the last page, and then generating the "Next"
        if ($pagenum != $last_page)
        {
            $next = $pagenum + 1;
            $paginationCtrls .= ' &nbsp; &nbsp; <a href="'.$_SERVER['PHP_SELF'].'?pn='.$next.'&amp;browse='.$browse.'">Next</a> ';
            // And add a last page link
            $paginationCtrls .= ' &nbsp; &nbsp; <a href="'.$_SERVER['PHP_SELF'].'?pn='.$last_page.'&amp;browse='.$browse.'">Last</a> ';
        }
    }

    // Build the table headers display
    if ($browse == 'title'){
       $table_part2 = "<tr><th id='title'>Title</th><th id='author'>Author</th><th id='pg' class='right'>PG No.</th></tr>\n";
    }elseif ($browse =='author_name'){
       $table_part2 = "<tr><th id='author'>Author</th><th id='title'>Title</th><th id='pg' class='right'>PG No.</th></tr>\n";
    }elseif ($browse =='postednum'){
       $table_part2 = "<tr><th id='pg' class='right'>PG No.</th><th id='author'>Author</th><th id='title'>Title</th></tr>\n";
    }

    // Build the display of the retrieved rows
    for ( $rownum=0; $rownum < $num_returned_rows; )
    {
        $finfo     = mysqli_fetch_array($result,MYSQLI_ASSOC);
        $id        = html_safe($finfo['id']);
        $author    = html_safe($finfo['author_name']);
        $title     = html_safe($finfo['title']);
        $postednum = html_safe($finfo['pg_identifier']);
  
        // Create table by title
        if ($browse == 'title')
        {
            $table_part3 .= "<tr><td><a href =\"{SiteBase}/tools/biblio.php?id=$id\">$title</a></td>";
            $table_part3 .= "<td>$author</td><td class='right'>$postednum</td></tr>";
        }

        if ($browse =='author_name')
        {
            $table_part3 .= "<tr><td>$author</td>";
            $table_part3 .= "<td><a href =\"{SiteBase}/tools/biblio.php?id=$id\">$title</a></td>";
            $table_part3 .= "<td class='right'>$postednum</td></tr>";
        }
        if ($browse =='postednum')
        {
            $table_part3 .= "<tr><td class='right'>$postednum</td><td>$author</td>";
            $table_part3 .= "<td><a href =\"{SiteBase}/tools/biblio.php?id=$id\">$title</a></td></tr>";
        }
        $rownum++;
     }

    $paging_header = "<div id='pagination_header'>".$header_1.$header_2."</div>\n";
    // Position the pagination controls
    $paginationCtrls = "<div id='pagination_controls'>".$paginationCtrls."</div>\n";

    $contents = $paging_header.$paginationCtrls.$table_part1.$table_part2.$table_part3.$table_part4; 
    return $contents;
}

// vim: sw=4 ts=4 expandtab
