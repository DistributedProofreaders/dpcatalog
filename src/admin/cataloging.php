<?php
$relPath="../includes/";
require_once($relPath.'common.inc');

// ==========================================================================
// Retrieve and sanitize URL parameters
// $id is the OLS id number of the title we're interested in

$id = validate_projectID( 'id', $_GET['id'], FALSE);

// ==========================================================================
// Set up page template

$page = new HtmlTemplate();
$page->IdentifyTemplate ($templatePath."PageTemplate.inc");

// ==========================================================================
// Fill in page template

$page->SetParameter ("PageTitle",       $site_name . " - ". _("Cataloging"));
$page->SetParameter ("PageHeader",      _("Cataloging"));
$page->SetParameter ("SiteBase",        $site_base);
$page->SetParameter ("ParentSiteBase",  $parent_url);
$page->SetParameter ("ParentSiteName",  $parent_name);
$page->SetParameter ("ProjectStats",    ProjectStats());
$page->SetParameter ("BookStats",       BookStats());
$page->SetParameter ("SearchBlock",     show_search(FALSE) );

$extras = array( array("text" => _("Bibliographic Record"), "url" => "../tools/biblio.php?id=$id", "active" => ""));

$page->SetParameter ("NavigationBlock", show_navbar('home', TRUE, TRUE, $extras ) );
$page->SetParameter ("PageBody",        displaymarc($id));
$page->SetParameter ("PageSubContent",  '');
$page->SetParameter ("BuildTime",       script_end_time($starttime));

$page->CreatePage();

// ==========================================================================

function displaymarc($id)
{
//main text block
//connect to database
//    $db_Connection = new dbConnect()
//        or die(_("Failed to connect to database."));

    $contents = "
           <table border =\"1\" cols = \"3\">
            <form action = \"cataloging.php\">
            <input type=\"hidden\" name=\"id\" value=\"$id\">
            <td colspan =\"3\" align = \"center\" bgcolor =\"#CCCCCC\"><b><u>Copyright Information</u></b></td><tr>
            <td width =\"18\"><b>Title:</b></td><td><input type='text' size='35' name='search'></td><td>(MARC Field 245, \$a)</td><tr>
            <td><b>Subtitle:</b></td><td><input type='text' size='35' name='search'></td><td>(MARC Field 245, \$b)</td><tr>
            <td><b>Author:</b></td><td><input type='text' size='35' name='search'></td><td>(MARC Field 100, \$a)</td><tr>
            <td><b>Author Birth/Death Dates:</b></td><td><input type='text' size='12' name='search'></td><td>(MARC Field 100, \$d)</td><tr>

            <td><b>Series Title (if applicable):</b></td><td><input type='text' size='12' name='search'></td><td>(MARC Field 440, \$a)</td><tr>
            <td><b>Copyright Date:</b></td><td><input type='text' size='12' name='search'></td><td>(MARC Field 008, positions 11-14)</td><tr>

            <td colspan =\"3\" align = \"center\" bgcolor =\"#CCCCCC\"><b><u>Publication Information</u></b><p></td><tr>
            <td><b>Publisher:</b></td><td><input type='text' size='35' name='search'></td><td>(MARC Field 260, \$b)</td><tr>
            <td><b>Publication Year:</b></td><td><input type='text' size='12' name='search'></td><td>(MARC Field 100, \$c and MARC Field 008, positions 07-10)</td><tr>
            <td><b>Place of Publication:</b></td><td><input type='text' size='35' name='search'></td><td>(MARC Field 260 \$a)</td><tr>


            <td><b>Illustrator:</b></td><td><input type='text' size='35' name='search'></td><td>(MARC Field 700, \$a, \$e (ill.))</td><tr>
            <td><b>Additional Authors (one per line):</b></td><td><input type='textarea' size='8' name='search'></td><td>(MARC Field 700, \$a)</td><tr>
            <td><b>Subject Headings (one per line):</b></td><td><input type='text' size='12' name='search'></td><td><b>(MARC Field 100, \$a)</b></td><tr>
            <td><b>Item Type:</b></td><td><input type='text' size='12' name='search'></td><td><b>(MARC Field 100, \$a)</b></td><tr>

            <td><b>Volume Title:</b></td><td><input type='text' size='12' name='search'></td><td>(MARC Field 440, \$a)</td><tr>
            <td><b>Volume Number:</b></td><td><input type='text' size='12' name='search'></td><td>(MARC Field 440, \$v)</td><tr>
            <td><b>Volume Description:</b></td><td><TEXTAREA NAME='100\$a' COLS=40 ROWS=6></TEXTAREA></td><td><b>(MARC Field 100, \$a)</b></td><tr>
            <td><b>Pages:</b></td><td><input type='text' size='6' name='search'></td><td><b>(MARC Field 100, \$a)</b></td><tr>
            <td><b>Size:</b></td><td><input type='text' size='12' name='search'></td><td><b>(MARC Field 100, \$a)</b></td><tr>
            <td><b>General Note:</b></td><td><TEXTAREA NAME='500\$a' COLS=60 ROWS=6></TEXTAREA></td><td>(MARC Field 500, \$a)</td><tr>
            <td><b>Summary:</b></td><td><TEXTAREA NAME='500\$a' COLS=60 ROWS=6></TEXTAREA></td><td>(MARC Field 520, \$a)</td><tr>

            <td align =\"center\" colspan = \"3\"><INPUT TYPE=SUBMIT VALUE='Save Record'></td><tr>
            </form>
            </table>
    ";


return $contents;
}

// vim: sw=4 ts=4 expandtab
