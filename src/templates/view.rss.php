<?php
require_once(__DIR__ . '/helpers.php');

//
// Search results as RSS Feed
//

header('Content-Type: text/xml; charset=utf-8', true);

$rss = new SimpleXMLElement('<rss xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:atom="http://www.w3.org/2005/Atom"></rss>');
$rss->addAttribute('version', '2.0');

$channel = $rss->addChild('channel'); //add channel node


// todo: if filters, add to title and desciption

$title = 'Newest ' . $limit . ' search results';
if ($query) $title = $query;

$title = $rss->addChild('title', $title); //title of the feed

$description = 'Newest ' . $limit . ' search results';
if ($query) $description .= ' for ' . $query;

$description = $rss->addChild('description', $description); //feed description

$link = $rss->addChild('link', $link);

//Create RFC822 Date format to comply with RFC822
$date_f = date("D, d M Y H:i:s T", time());
$build_date = gmdate(DATE_RFC2822, strtotime($date_f));
$lastBuildDate = $rss->addChild('lastBuildDate',$date_f);

$generator = $rss->addChild('generator','Open Semantic Search: https://opensemanticsearch.org');

$result_nr = 0;

foreach ($results->response->docs as $doc) {

      $result_nr++;
      $id = $doc->id;

      // Type
      $type = $doc->content_type_ss;

      // URI

      // if part of container like zip, link to container file
      // if PDF page URI to Deeplink
      // since PDF Reader can open deep links
      if (isset($doc->container_s) and $type != 'PDF page') {
        $uri = $doc->container_s;

      }
      else {
        $uri = $id;
      }

      // Author
      $author = $doc->author_ss;

      // Title
      $title = format_title($doc->title_txt, $uri_label);

      // Modified date
      $datetime = FALSE;
      if (isset($doc->file_modified_dt)) {
        $datetime = $doc->file_modified_dt;
      }
      elseif (isset($doc->last_modified_dt)) {
        $datetime = $doc->last_modified_dt;
      }

      $snippets = array();

      if (isset($results->highlighting->$id->content_txt)) {
        $snippets = $results->highlighting->$id->content_txt;
      }

      foreach ($cfg['languages'] as $language) {
        $language_specific_fieldname = 'content_txt_txt_' . $language;
        if (isset($results->highlighting->$id->$language_specific_fieldname)) {
          $snippets = $results->highlighting->$id->$language_specific_fieldname;
        }
      }

      if (count($snippets) === 0 && isset($doc->content_txt)) {
        // if no snippets available, use content as snippet
        $snippets = array($doc->content_txt);
        // and cut it to snippet size
        if (strlen($snippets[0]) > $cfg['snippetsize']) {
          $snippets[0] = substr($snippets[0], 0, $cfg['snippetsize']) . "...";
        }
      }

      $item = $rss->addChild('item');
      $title = $item->addChild('title', $title);
      $link = $item->addChild('link', $uri);
      $guid = $item->addChild('guid', $uri);
      $guid->addAttribute('isPermaLink', 'false');
       
      $description = $item->addChild('description', implode("<hr>", $snippets));
       
      $date_rfc = gmdate(DATE_RFC2822, strtotime($datetime));
      $item = $item->addChild('pubDate', $date_rfc); //add pubDate nod

}

echo $rss->asXML();

?>
