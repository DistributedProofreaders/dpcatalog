<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
    <link rel="stylesheet" type="text/css" href="{SiteBase}/includes/ols.css" media="screen, projection" />
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
    <title>{PageTitle}</title>
</head>
<body>

<div id="metacontainer">

    <div id="container">

    <!-- Headerbar block -->
        <div id="header">
            <a href="{SiteBase}/index.php">
            <img src="{SiteBase}/graphics/olslogo.jpg"
            alt="{SiteName}"
            title="{SiteName}" /></a>
        </div>

        <div id="subheader">

            <!-- Search block -->
            {SearchBlock}

            <!-- Navigation block -->
            {NavigationBlock}

        </div>

    <!-- Main block -->

           <div id="content">
            <h2>{PageHeader}</h2>
            {PageBody}
        </div>

        <div id="subcontent">
            {PageSubContent}
        </div>

    <!-- Footer block -->

        <div id="footer">

            <img id = 'footer_img' src="{SiteBase}/graphics/rbhead.jpg"
                alt="Jan Davidsz de Heem: Still-Life of Books (1628)"
                title="Jan Davidsz de Heem: Still-Life of Books (1628)" />

            <h3>{ProjectStats} Archived Projects ({BookStats} Unique Titles)</h3>
            <p>Web site design and supporting code are copyrighted by: <a href="{ParentSiteBase}">{ParentSiteName}</a>. <span class="tiny">{BuildTime}</span></p>
        </div>
    </div>
</div>

</body>
</html>
