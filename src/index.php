<?php
$relPath="./includes/";
require_once($relPath.'common.inc');

// ==========================================================================
// Set up page template

$page = new HtmlTemplate();
$page->IdentifyTemplate ($templatePath."PageTemplate.inc");

// ==========================================================================
// Fill in page template

$page->SetParameter ("PageTitle",       $site_name);
$page->SetParameter ("PageHeader",      _("Welcome to the Open Library System."));
$page->SetParameter ("SiteBase",        $site_base);
$page->SetParameter ("ParentSiteBase",  $parent_url);
$page->SetParameter ("ParentSiteName",  $parent_name);
$page->SetParameter ("SearchBlock",     show_search(TRUE) );
$page->SetParameter ("NavigationBlock", show_navbar('home', TRUE, FALSE) );

// Display most recently added titles
$body_html = MostRecent();

$body_html .= <<<BODY_HTML
<h3>About This Site:</h3>
<p>This site is for the development and testing of the $site_abbr code.</p>

<p>This first implementation of $site_abbr receives project files
for text, page images and illustrations of ebooks completed by
<a href="$parent_url">$parent_name</a>.</p>

<p>It also provides several interfaces for searching, browsing, and displaying
files pertaining to these ebooks.</p>

<p>Plans for future versions of the $site_abbr code include the addition
of administrative tools for the import and maintenance of new projects as they
are completed, as well as the incorporation of additional cataloging
information for each title.</p>
BODY_HTML;

$page->SetParameter ("PageBody",        $body_html);
$page->SetParameter ("PageSubContent",  $site_disclaimer);
$page->SetParameter ("ProjectStats",    ProjectStats());
$page->SetParameter ("BookStats",       BookStats());
$page->SetParameter ("BuildTime",       script_end_time($starttime));

$page->CreatePage();

// vim: sw=4 ts=4 expandtab
?>

