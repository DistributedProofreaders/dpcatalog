<?php
/*
* Parse the nodes of a simplexml element and return a string
* containing the concatenated data.
*
* @param         $xml              (a simplexml object)
* @param  string $xpath            (xml path to point of interest)
* @param  string $output_separator (optional output separator)
* @return string
*/

function get_node_info($xml, $xpath, $output_separator = PHP_EOL)
{
    // Returns a string
    $info = '';
    $nodes = $xml->xpath($xpath);

    if ( $nodes === FALSE ) { continue; }
    else
    {
        foreach ($nodes as $node)
        {
            // Cast as (string) since PHP treats XML elements as objects
            $info .= (string)$node . $output_separator;
        }
    }

    // Remove any extraneous trailing separators we may have added
    return rtrim($info, $output_separator);
}

/*
* Find the XML/RDF catalog entry file for a given PG ID and extract
* selected node data, returning an array.
*
* Note that the Project Gutenberg .rdf files are UTF-8 encoded.
* 
* @param  int    $pg_id          (the PG identifier)
* @param  string $pg_catalog_dir (filesystem path to catalog directory)
* @return array
*/

function parse_pg_catalog_rdf($pg_id, $pg_catalog_dir)
{
    // Construct the fully-qualified path to the expected catalog file
    $pg_file = 'pg'.$pg_id.'.rdf';
    $path    = $pg_catalog_dir.$pg_file;

    // Set up array to return the results
    $results = array();

    // If we can't find the catalog file, return an empty array.
    if ( !file_exists($path))
    {
        return $results;
    }

    // Pull in the catalog XML
    $root = simplexml_load_file($path);

    $results['id'] = $pg_id;

    // The Project Gutenberg link for the title in question can be constructed
    // from  the root note <rdf:RDF ... > by extracting the xml:base attribute
    // value and combining that with the rdf:about attribute value from the
    // <pgterms:ebook  ... > child node.
    $pg_base  = '';
    $pg_about = '';
    $pg_base  = (string)$root->attributes("xml", true)->base;
    $pg_about = (string)$root->children('pgterms', true)->ebook->attributes("rdf",true)->about;
    $results['pg_link'] = $pg_base.$pg_about;

    // Title of work
    $title_xpath = '/rdf:RDF/pgterms:ebook/dcterms:title';
    // TODO: utf8_decode() call is temporary display protection
    // mb_convert() called in this fashion works as expected., iconv() breaks on unexpected input
    $results['title'] = mb_convert_encoding(get_node_info($root,$title_xpath), 'HTML-ENTITIES', "UTF-8");

    // Author(s)
    // May also want to pick up the pgterms:alias nodes
    $name_xpath = '/rdf:RDF/pgterms:ebook/dcterms:creator/pgterms:agent/pgterms:name';
    // TODO: utf8_decode() call is temporary display protection
    $results['name'] = utf8_decode(get_node_info($root,$name_xpath,'; '));

    // Subject(s)
    $subject_xpath = '/rdf:RDF/pgterms:ebook/dcterms:subject/rdf:Description/rdf:value';
    $results['subject'] = get_node_info($root,$subject_xpath,', ');

    // Language(s)
    $language_xpath = '/rdf:RDF/pgterms:ebook/dcterms:language/rdf:Description/rdf:value';
    $results['language'] = get_node_info($root,$language_xpath,', ');

    $rights_xpath = '/rdf:RDF/pgterms:ebook/dcterms:rights';
    $results['rights'] = get_node_info($root,$rights_xpath);

    // PG Bookshelves
    $shelf_xpath = '/rdf:RDF/pgterms:ebook/pgterms:bookshelf/rdf:Description/rdf:value';
    // TODO: utf8_decode() call is temporary display protection
    $results['shelves'] = utf8_decode(get_node_info($root,$shelf_xpath));

    return $results;
}

// vim: sw=4 ts=4 expandtab

