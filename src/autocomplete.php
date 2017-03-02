<?php
/*

	autocomplete.php - List of words for autocompletition

   Version 15.03.14 by Markus Mandalka
*/

  header('Content-Type: application/json; charset=utf-8');

  
  $cfg['solr']['host'] = 'localhost';
  $cfg['solr']['port'] = 8983;
  $cfg['solr']['path'] = '/solr';
  
  $cfg['solr']['core'] = 'core1';

  // include configs
  
  include 'config/config.php';
  
  // build solr uri from config  
  $solruri = 'http://' . $cfg['solr']['host'] . ':' . $cfg['solr']['port'] . $cfg['solr']['path'] . '/' . $cfg['solr']['core'];
  
  $limit = 15;

  $query = (string)$_GET["query"];

  $uri = $solruri . '/terms?terms.fl=_text_&terms.limit=' . $limit . '&terms.prefix=' . urlencode(strtolower($query));
  $result = file_get_contents($uri);

  $termsxml = simplexml_load_string($result);
	
  echo "{ query:'" . $query . "', suggestions:[";

  $first = true;
  foreach($termsxml->lst[1]->lst->int as $term) {

	if ($first) { $first=false; } else { echo ","; }
	 echo "'" . $term['name'] . "'";

  }
  
  echo ' ] }';
?>