<?php
/*

	autocomplete.php - List of words for autocompletition

*/

  header('Content-Type: application/json; charset=utf-8');

  if (getenv('SOLR_PHP_UI_SOLR_HOST')) {
    $cfg['solr']['host'] = getenv('SOLR_PHP_UI_SOLR_HOST');
  } else {
    $cfg['solr']['host'] = 'localhost';
  }

  $cfg['solr']['port'] = 8983;
  $cfg['solr']['path'] = '/solr';
  
  $cfg['solr']['core'] = 'opensemanticsearch';

  // include configs
  
  include 'config/config.php';
  
  // build solr uri from config  
  $solruri = 'http://' . $cfg['solr']['host'] . ':' . $cfg['solr']['port'] . $cfg['solr']['path'] . '/' . $cfg['solr']['core'];
  
  $limit = 15;

  $query = (string)$_GET["query"];

  $uri = $solruri . '/terms?wt=xml&terms.fl=_text_&terms.limit=' . $limit . '&terms.prefix=' . urlencode(strtolower($query));
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
