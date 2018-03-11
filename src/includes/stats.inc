<?php

function ProjectStats()
{
// Connect to database
    $db_Connection = new dbConnect()
        or die(_("Failed to connect to database."));
    $sql = "SELECT
            count(id)
            FROM catalog";
    $result = mysqli_query( $db_Connection->connection, $sql);
    list($num) = mysqli_fetch_row($result);
    return $num;
}

function BookStats()
{
    $db_Connection = new dbConnect()
        or die(_("Failed to connect to database."));
    $sql = "SELECT
            count(DISTINCT pg_identifier)
            FROM catalog";
    $result = mysqli_query( $db_Connection->connection, $sql);
    list($num) = mysqli_fetch_row($result);
    return $num;
}

function MostRecent()
{
    $db_Connection = new dbConnect()
        or die(_("Failed to connect to database."));
    $sql = "SELECT
            id, CAST(pg_identifier AS UNSIGNED) as pg_id, author_name, title
            FROM catalog
            ORDER BY pg_id DESC LIMIT 7";
    $result = mysqli_query( $db_Connection->connection, $sql);
    $num_rows = mysqli_num_rows($result);

    $recent_header = '<h3>' . _("Most Recently Added Titles:") . '</h3>';

    $table_part1 = "<table id='search_table'>\n";
    $table_part1 .= "<tr><th class='left'>"._("Title")."</th><th class='left'>"._("Author")."</th><th id='pg' class='right'>"._("PG No.")."</th></tr>";
    $table_part2 = '';

    for ( $rownum=0; $rownum < $num_rows; )
    {
        $finfo  = mysqli_fetch_array($result,MYSQLI_ASSOC);
        $id     = html_safe($finfo['id']);
        $author = html_safe($finfo['author_name']);
        $title  = html_safe($finfo['title']);
        $pg_id  = html_safe($finfo['pg_id']);

        $table_part2 .= "<tr><td><a href =\"tools/biblio.php?id=$id\">$title</a></td><td>$author</td><td class=\"right\">$pg_id</td></tr>\n";
        $rownum++;
    }

    $table_part3 = "</table>";

    $table = $table_part1.$table_part2.$table_part3;

    return $recent_header.$table;
}

// vim: sw=4 ts=4 expandtab
