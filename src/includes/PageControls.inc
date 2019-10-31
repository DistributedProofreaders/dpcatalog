<?php
// This file provides a set of routines used to create/display various page controls

function show_search( $display = TRUE )
{
    global $site_base;
    if ( !$display ) return "";

    return "<div id='search'>
                <form action = '$site_base/tools/search.php'>
                <div>Search:&nbsp;<input type='text' size='25' name='search' />
                <select name='searchtype'>
                <option label='Title' value='title'>Title</option>
                <option label='Author' value='author_name'>Author</option>
                <option label='PG Number' value='pg_identifier'>PG Number</option>
                <option label='DP ProjectID' value='dpprojectid'>DP Project ID</option>
                </select>&nbsp;<input type='submit' value='Search' /></div></form>
                <div class='right'>(All searches are 'wild card' searches.)</div>
            </div>
    ";
}

function show_navbar( $active = 'home', $display = TRUE, $home_only = FALSE, $extras = array() )
{
    global $site_base;
    if ( !$display ) return "";

    $navblock  = "<ul id='navigation'>";
    $class = ( ($active == 'home') ? " class='active'" : ""); //1
    $navblock .= "<li$class> <a href='$site_base/index.php'>Home</a></li>";

    // Add any optional extra navigational elements, passed in as a multidimensional array,
    // without preset keys, values are arranged as array( "text" => <name>, "url" = <url>,
    // "active" = <yes/no>)

    foreach ($extras as $navitem) {
        $item_name = $navitem["text"];
        $item_url  = $navitem["url"];
        $item_active = $navitem["active"];
        $class = ( ($active == 'yes') ? " class='active'" : "");
        $navblock .= "<li$class> <a href='$item_url'>$item_name</a></li>";
    }

    if ( $home_only ) return $navblock."</ul>";

    $class = ( ($active == 'br_title') ? " class='active'" : ""); //2
    $navblock .= "<li$class> <a href='$site_base/tools/browse.php?browse=title'>Browse Titles</a></li>";
    $class = ( ($active == 'br_author') ? " class='active'" : ""); //3
    $navblock .= "<li$class> <a href='$site_base/tools/browse.php?browse=author_name'>Browse Authors</a></li>";
    $class = ( ($active == 'br_pgid') ? " class='active'" : ""); //4
    $navblock .= "<li$class> <a href='$site_base/tools/browse.php?browse=postednum'>Browse by PG IDs</a></li>";
    $navblock .= "</ul>";

    return $navblock;
}
